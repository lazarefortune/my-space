<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Entity\View;
use App\Form\StoryType;
use App\Entity\Parameters;
use App\Entity\Inspiration;
use App\Form\CommentaryType;
use App\Entity\CommentaryStory;
use Doctrine\ORM\EntityManager;
use App\Repository\ViewRepository;
use App\Repository\ParametersRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\InspirationRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
/**
 * @Route("/story",name="story_")
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
    public function create(Request $request, \Swift_Mailer $mailer, InspirationRepository $inspirationRepository): Response
    {
        $story = new Inspiration();

        $form = $this->createForm(StoryType::class, $story);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $storyExist = $inspirationRepository->findByTitle($story->getTitle());
            if ( $storyExist ) {
                $this->addFlash('danger', 'Une story existe déjà avec ce titre');
                return $this->redirectToRoute('story_create');
            }

            // On attribue l'auteur à la story
            $story->setIdUser($this->getUser());
            // dd($story->getStatut());
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
                    $user = $parameter->getIdUser();
                    $toEmail[] = $user->getEmail();
                    if ( $user->getSecondEmail() ) {
                        $toEmail[] = $user->getSecondEmail();
                    }
                }
                // $toEmail = ["lazarefortune@gmail.com"];
                // $toEmail = ["lazarefortune@gmail.com", "jessyjess00021@gmail.com", "jessicatemba.s@gmail.com"];
                $messageTitle = 'Nouvelle story n°' . $story->getId() . ' disponible';
                // dd($messageTitle);
                $message = (new \Swift_Message($messageTitle))
                    // On attribue l'expéditeur
                    ->setFrom('myspace@lazarefortune.com')
                    // On attribue le destinataire
                    ->setTo($toEmail)
                    // On crée le texte avec la vue
                    ->setBody(
                        $this->renderView(
                            'layouts/emails/contact.html.twig',
                            compact('story')
                        ),
                        'text/html'
                    );
                $mailer->send($message);
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
    public function show($storyId, \Swift_Mailer $mailer, Request $request): Response
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
            // dd( $commentary );
            $this->addFlash('success', 'Commentaire ajouté avec succès');
            return $this->redirectToRoute('story_show', ['storyId' => $story->getId()]);
            // return $this->redirect($this->generateUrl('story_show', array('storyId' => $story)));
        }

        $repo = $this->getDoctrine()->getRepository(Parameters::class);
        $result = $repo->findOneBy([
            'idUser' => $this->getUser()->getIdUser(),
        ]);
        if (is_null($result)) {
            $canCountView = false;
        } else {
            $canCountView = $result->getViewCounter();
        }

        $isSuperAdmin = $this->isGranted('ROLE_SUPER_ADMIN');
        if ($this->getUser()->getIdUser() != $story->getIdUser()->getIdUser() && !$isSuperAdmin && $canCountView) {

            $view = new View();
            $view->setIdUser($this->getUser());
            $view->setIdStory($story);
            $view->setDate(new \DateTime('now', new \DateTimeZone('Europe/Paris')));
            $isDeleteStory = $story->getTrash();
            if ($isDeleteStory) {
                $view->setCommentary("La story est en corbeille (inaccessible)");
            }
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($view);
            $entityManager->flush();

            // Envoie de l'email
            $userName = $this->getUser()->getNom() . " " . $this->getUser()->getPrenom();
            $toEmail = ["lazarefortune@gmail.com"];
            $messageTitle = 'Nouvelle vue sur story n°' . $story->getId();
            $message = (new \Swift_Message($messageTitle))
                // On attribue l'expéditeur
                ->setFrom('myspace@lazarefortune.com')
                // On attribue le destinataire
                ->setTo($toEmail)
                // On crée le texte avec la vue
                ->setBody(
                    $this->renderView(
                        'layouts/emails/viewStory.html.twig',
                        compact('story', 'userName', 'view')
                    ),
                    'text/html'
                );
            $mailer->send($message);
        }

        if ($this->getUser()->getIdUser() != $story->getIdUser()->getIdUser() and $story->getStatut() == "privee") {
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
    public function edit($storyId, Request $request, \Swift_Mailer $mailer, InspirationRepository $inspirationRepository)
    {
        $repo = $this->getDoctrine()->getRepository(Inspiration::class);
        $story = $repo->find($storyId);

        $hasAccess = $this->isGranted('ROLE_ADMIN');
        if ($this->getUser()->getIdUser() != $story->getIdUser()->getIdUser() && !$hasAccess) {
            return $this->denyAccessUnlessGranted('ROLE_EDIT', $story, 'Action non autorisée.');
        }

        $form = $this->createForm(StoryType::class, $story);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($story);
            $entityManager->flush();

            $story = $inspirationRepository->findByTitle($story->getTitle());

            if ($story->getStatut() == "public") {

                // on recherche les utilisateurs souhaitant des emails
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
                // $toEmail = ["lazarefortune@gmail.com"];
                // $toEmail = ["lazarefortune@gmail.com", "jessyjess00021@gmail.com", "jessicatemba.s@gmail.com"];
                $messageTitle = 'Modification de la story n°' . $story->getId() . ' disponible';
                $message = (new \Swift_Message($messageTitle))
                    // On attribue l'expéditeur
                    ->setFrom('myspace@lazarefortune.com')
                    // On attribue le destinataire
                    ->setTo($toEmail)
                    // On crée le texte avec la vue
                    ->setBody(
                        $this->renderView(
                            'layouts/emails/updateStory.html.twig',
                            compact('story')
                        ),
                        'text/html'
                    );
                $mailer->send($message);
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
