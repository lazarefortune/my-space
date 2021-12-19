<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Entity\View;
use App\Form\StoryType;
use App\Entity\MailSend;
use App\Entity\Parameters;
use App\Entity\Inspiration;
use App\Form\CommentaryType;
use App\Form\StoryCreateType;
use App\Entity\CommentaryStory;
use Doctrine\ORM\EntityManager;
use App\Repository\ViewRepository;
use App\Repository\ParametersRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\InspirationRepository;
use App\Services\SendMailService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
/**
 * @Route("/story",name="story_")
 * @Security("is_granted('ROLE_USER')")
 */
class StoryController extends AbstractController
{
    /**
     * @Route("/public", name="index")
     */
    public function index(InspirationRepository $inspirationRepository): Response
    {

        $allStory = $inspirationRepository->findBy([
            'statut' => ["public", "public_anonyme"],
            'trash' => false
        ], [
            'created_at' => 'DESC'
        ]);
        return $this->render('admin/story/inspiration.html.twig', [
            'allStory' => $allStory,
        ]);
    }

    /**
     * @Route("/my", name="my")
     */
    public function my_stories(InspirationRepository $inspirationRepository): Response
    {
        $allStory = $inspirationRepository->findBy([
            'idUser' => $this->getUser()->getIdUser(),
            'trash' => false
        ], [
            'created_at' => 'DESC'
        ]);
        return $this->render('admin/story/my_inspiration.html.twig', [
            'allStory' => $allStory,
        ]);
    }

    /**
     * @Route("/create", name="create")
     */
    public function create(Request $request, SendMailService $mailer, InspirationRepository $inspirationRepository): Response
    {
        $story = new Inspiration();

        $form = $this->createForm(StoryCreateType::class, $story);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // Rechercher une story avec le même titre...
            // TODO : Revoir la condition d'unicité des stories
            $storyExist = $inspirationRepository->findByTitle($story->getTitle());
            if ( $storyExist ) {
                $this->addFlash('danger', 'Une story existe déjà avec ce titre');
                return $this->redirectToRoute('story_create');
            }

            // On attribue l'auteur à la story
            $story->setIdUser($this->getUser());
            // On définie l'heure
            $story->setCreatedAt(new \DateTime('now', new \DateTimeZone('Europe/Paris')));

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($story);
            $entityManager->flush();

            $story = $inspirationRepository->findByTitle($story->getTitle());
            if ($story->getStatut() == "public") {

                // on recherche les emails de destination
                $parameters = $this->getDoctrine()
                    ->getRepository(Parameters::class)
                    ->findBy([
                        "emailNotifications" => "true",
                    ]);
                $toEmail = [];
                foreach ($parameters as $parameter) {
                    $userMail = $parameter->getIdUser();
                    $toEmail[] = $userMail->getEmail();
                    if ( $userMail->getSecondEmail() ) {
                        $toEmail[] = $userMail->getSecondEmail();
                    }
                }

                $mailer->sendMail(
                    $this->getParameter('send_mail_user'),
                    $toEmail,
                    "Nouvelle story publiée",
                    "story/new_story",
                    [
                        "story" => $story,
                        "user" => $this->getUser()
                    ]
                );
            }

            $this->addFlash('success', 'Nouvelle story ajoutée avec succès');

            return $this->redirectToRoute('story_index');
        }

        return $this->render('admin/story/create.html.twig', [
            'formStory' => $form->createView()
        ]);
    }

    /**
     * @Route("/show/{storyId}", name="show")
     */
    public function show($storyId, Request $request, SendMailService $mailer ): Response
    {
        $repo = $this->getDoctrine()->getRepository(Inspiration::class);
        $story = $repo->find($storyId);
        $commentaries =  $story->getCommentaryStories();

        $commentary = new CommentaryStory();

        $form = $this->createForm(CommentaryType::class, $commentary);
        $form->handleRequest($request);

        // Si l'utilisateur a posté un commentaire
        if ($form->isSubmitted() && $form->isValid()) {
            $commentary->setUser($this->getUser());
            $commentary->setStory($story);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($commentary);
            $entityManager->flush();

            // On notifie l'auteur de la story uniquement si la story est publiée
            if ( $commentary->getUser()->getIdUser() != $story->getIdUser()->getIdUser() && ( $story->getStatut() == "public" || $story->getStatut() == "public_anonyme" ) ) {
                
                $mailer->sendMail(
                    $this->getParameter('send_mail_user'),
                    [ $story->getIdUser()->getEmail() ],
                    'Nouveau commentaire sur votre story',
                    'story/new_commentary',
                    [
                        'commentary' => $commentary,
                        'story' => $story,
                        'user' => $this->getUser(),
                    ]
                );
            }

            $this->addFlash('success', 'Commentaire ajouté avec succès');
            return $this->redirectToRoute('story_show', ['storyId' => $story->getId()]);
        }
        // On vérifie si on peut compter la vue de l'utilisateur sur la story
        $repo = $this->getDoctrine()->getRepository(Parameters::class);
        $result = $repo->findOneBy([
            'idUser' => $this->getUser()->getIdUser(),
        ]);
        if (is_null($result)) {
            $canCountView = false;
        } else {
            $canCountView = $result->getViewCounter(); // (true)
        }

        $isSuperAdmin = $this->isGranted('ROLE_SUPER_ADMIN');
        $isAuthorStory = $this->getUser()->getIdUser() == $story->getIdUser()->getIdUser();
        if ( !$isAuthorStory && !$isSuperAdmin && $canCountView) {

            $view = new View();
            $view->setIdUser($this->getUser());
            $view->setIdStory($story);
            $view->setDate(new \DateTime('now', new \DateTimeZone('Europe/Paris')));
            $isDeleteStory = $story->getTrash();
            if ( $isDeleteStory ) {
                $view->setCommentary("La story est en corbeille (inaccessible)");
            }
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($view);
            $entityManager->flush();

            // Informations pour le mail
            $userName = $this->getUser()->getNom() . " " . $this->getUser()->getPrenom();
            $toEmail = [
                $this->getParameter( 'admin_email' )
            ];
            $messageTitle = 'Nouvelle vue sur la story n°' . $story->getId();
            $context = [
                'story' => $story,
                'userName' => $userName,
                'view' => $view
            ];
            // Envoie de l'email
            $mailer->sendMail(
                $this->getParameter( 'send_mail_user' ),
                $toEmail, 
                $messageTitle, 
                "viewStory", 
                $context 
            );

            // On enregistre en BDD le mail envoyé
            // $email = new MailSend();
            // $email->setAuthor( $this->getUser() );
            // $email->setTitle( $messageTitle );
            // $email->setToEmail( implode( ",", $toEmail ) );
            // $email->setContent( $message->getBody() );
            // $email->setFromEmail( "myspace@lazarefortune.com" );
            // dd( $email->getContent() );
            // $entityManager = $this->getDoctrine()->getManager();
            // $entityManager->persist($email);
            // $entityManager->flush();
        }

        // TODO : Revoir le code de cette partie
        if ( !$isAuthorStory && $story->getStatut() == "privee") {
            return $this->denyAccessUnlessGranted('ROLE_EDIT', $story, 'Vous n\'avez pas le droit de consulter cette story.');
        }

        return $this->render('admin/story/show.html.twig', [
            "story" => $story,
            "commentaries" => $commentaries,
            "formCommentary" => $form->createView()
        ]);
    }

    /**
     * @Route("/edit/{storyId}", name="edit")
     */
    public function edit($storyId, Request $request, SendMailService $mailer, InspirationRepository $inspirationRepository)
    {
        $repo = $this->getDoctrine()->getRepository(Inspiration::class);
        $story = $repo->find($storyId);

        $hasAccess = $this->isGranted('ROLE_ADMIN');
        if ($this->getUser()->getIdUser() != $story->getIdUser()->getIdUser() && !$hasAccess) {
            return $this->denyAccessUnlessGranted('ROLE_EDIT', $story, 'Action non autorisée.');
        }

        $form = $this->createForm(StoryType::class, $story);
        $form->handleRequest($request);

        if ( $form->isSubmitted() && $form->isValid() ) {

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($story);
            $entityManager->flush();

            $story = $inspirationRepository->findByTitle($story->getTitle());

            if ($story->getStatut() == "public") {

                // on recherche les utilisateurs souhaitant recevoir des mails
                $parameters = $this->getDoctrine()
                    ->getRepository(Parameters::class)
                    ->findBy([
                        "emailNotifications" => "true",
                    ]);
                $toEmail = [];
                foreach ($parameters as $parameter) {
                    $user = $parameter->getIdUser();
                    $toEmail[] = $user->getEmail();
                    if ( $user->getSecondEmail() ) {
                        $toEmail[] = $user->getSecondEmail();
                    }
                }

                $messageTitle = 'Modification de la story n°' . $story->getId() . ' disponible';
                $mailer->sendMail(
                    $this->getParameter( 'send_mail_user' ),
                    $toEmail,
                    $messageTitle,
                    "updateStory",
                    [
                        'story' => $story,
                    ]
                );
            }

            $this->addFlash('success', 'Story mis à jour avec succès');

            return $this->redirectToRoute('story_index');
        }

        return $this->render('admin/story/edit.html.twig', [
            'formStory' => $form->createView(),
            "story" => $story
        ]);
    }

    /**
     * @Route("/trash", name="trash")
     */
    public function my_trash(InspirationRepository $inspirationRepository): Response
    {
        $allStory = $inspirationRepository->findBy([
            'idUser' => $this->getUser()->getIdUser(),
            'trash' => true
        ], [
            'created_at' => 'DESC'
        ]);
        return $this->render('admin/story/my_trash.html.twig', [
            'allStory' => $allStory,
        ]);
    }

    /**
     * @Route("/trash/add/{storyId}" , name="move_to_trash")
     */
    public function move_trash($storyId, EntityManagerInterface $entityManager)
    {
        $story = $entityManager->getRepository(Inspiration::class)->find($storyId);
        $isInTrash = $story->getTrash();

        if ($isInTrash) {
            $story->setTrash(false);
            $this->addFlash('info', 'La story a été restauré de la corbeille avec succès');
        } else {
            $story->setTrash(true);
            $this->addFlash('warning', 'La story a été mise à la corbeille avec succès');
        }
        $entityManager->flush();
        return $this->redirectToRoute('story_my');
    }

    /**
     * @Route("/delete/{storyId}", name="delete")
     */
    public function delete($storyId, EntityManagerInterface $entityManager): Response
    {
        $story = $entityManager->getRepository(Inspiration::class)->find($storyId);

        $hasAccess = $this->isGranted('ROLE_ADMIN');
        if ( $this->getUser()->getIdUser() != $story->getIdUser()->getIdUser() &&  !$hasAccess ) {
            return $this->denyAccessUnlessGranted('ROLE_DELETE', $story, 'Action non autorisée.');
        }

        $entityManager->remove($story);
        $entityManager->flush();
        $this->addFlash('success', 'La story a été supprimé avec succès');
        return $this->redirectToRoute('story_my');
    }
}
