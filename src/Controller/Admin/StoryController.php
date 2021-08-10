<?php

namespace App\Controller\Admin;

use App\Entity\Inspiration;
use App\Form\StoryType;
use App\Repository\InspirationRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class StoryController extends AbstractController
{
    /**
     * @Route("/my-space/inspiration", name="inspiration")
     */
    public function index(InspirationRepository $inspirationRepository): Response
    {

        $allStory = $inspirationRepository->findBy([
            'statut' => "public"
        ]);
        return $this->render('admin/story/inspiration.html.twig', [
            'allStory' => $allStory,
        ]);
    }

    /**
     * @Route("/my-space/inspiration/mes-stories", name="my_inspiration")
     */
    public function my_stories( InspirationRepository $inspirationRepository ) : Response
    {
        $allStory = $inspirationRepository->findBy([
            'idUser' => $this->getUser()->getIdUser()
        ]);
        return $this->render('admin/story/my_inspiration.html.twig', [
            'allStory' => $allStory,
        ]);
    }

    /**
     * @Route("/my-space/inspiration/create", name="create_inspiration")
     */
    public function create(Request $request, \Swift_Mailer $mailer, InspirationRepository $inspirationRepository): Response
    {
        $story = new Inspiration();

        $form = $this->createForm(StoryType::class, $story);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // On attribue l'auteur à la story
            $story->setIdUser( $this->getUser() );
            // dd($story);
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($story);
            $entityManager->flush();

            $story = $inspirationRepository->findByTitle($story->getTitle());
            if ( $story->getStatut() == "public" ) {
                
                $toEmail = ["lazarefortune@gmail.com", "jessyjess00021@gmail.com", "jessicatemba.s@gmail.com"];
    
                $messageTitle = 'Nouvelle story n°'.$story->getId().' disponible';
                dd($messageTitle);
                $message = (new \Swift_Message( $messageTitle ))
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

            return $this->redirectToRoute('inspiration');
        }

        return $this->render('admin/story/create.html.twig', [
            'formStory' => $form->createView()
        ]);
    }

    /**
     * @Route("/my-space/inspiration/show/{storyId}", name="show_inspiration")
     */
    public function show($storyId): Response
    {
        $repo = $this->getDoctrine()->getRepository(Inspiration::class);
        $story = $repo->find($storyId);
        
        if ( $this->getUser()->getIdUser() != $story->getIdUser()->getIdUser() AND $story->getStatut() == "privee" ) {
            return $this->denyAccessUnlessGranted('ROLE_EDIT', $story, 'Vous n\'avez pas le droit de consulter cette story.');
        }

        return $this->render('admin/story/show.html.twig', [
            "story" => $story
        ]);
    }

    /**
     * @Route("/my-space/inspiration/edit/{storyId}", name="edit_inspiration")
     */
    public function edit( $storyId , Request $request, \Swift_Mailer $mailer, InspirationRepository $inspirationRepository)
    {
        $repo = $this->getDoctrine()->getRepository(Inspiration::class);
        $story = $repo->find($storyId);

        if ( $this->getUser()->getIdUser() != $story->getIdUser()->getIdUser() ) {
            return $this->denyAccessUnlessGranted('ROLE_EDIT', $story, 'Vous n\'avez pas le droit de consulter cette story.');
        }

        $form = $this->createForm(StoryType::class, $story);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($story);
            $entityManager->flush();

            $story = $inspirationRepository->findByTitle($story->getTitle());

            if( $story->getStatut() == "public" )
            {
                $toEmail = ["lazarefortune@gmail.com", "jessyjess00021@gmail.com", "jessicatemba.s@gmail.com"];
                $messageTitle = 'Modification de la story n°'.$story->getId().' disponible';
                $message = (new \Swift_Message( $messageTitle ))
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

            return $this->redirectToRoute('inspiration');
        }

        return $this->render('admin/story/edit.html.twig', [
            'formStory' => $form->createView(),
            "story" => $story
        ]);

    }
}
