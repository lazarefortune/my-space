<?php

namespace App\Controller\Admin;

use App\Form\StoryType;
use App\Entity\Inspiration;
use App\Entity\Parameters;
use App\Entity\View;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\InspirationRepository;
use App\Repository\ParametersRepository;
use App\Repository\ViewRepository;
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
            'statut' => ["public", "public_anonyme"]
        ]);
        return $this->render('admin/story/inspiration.html.twig', [
            'allStory' => $allStory,
        ]);
    }

    /**
     * @Route("/my-space/inspiration/mes-stories", name="my_inspiration")
     */
    public function my_stories(InspirationRepository $inspirationRepository): Response
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
            $story->setIdUser($this->getUser());
            // dd($story);
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($story);
            $entityManager->flush();

            $story = $inspirationRepository->findByTitle($story->getTitle());
            if ($story->getStatut() == "public") {

                $toEmail = ["lazarefortune@gmail.com", "jessyjess00021@gmail.com", "jessicatemba.s@gmail.com"];

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

            return $this->redirectToRoute('inspiration');
        }

        return $this->render('admin/story/create.html.twig', [
            'formStory' => $form->createView()
        ]);
    }

    /**
     * @Route("/my-space/inspiration/show/{storyId}", name="show_inspiration")
     */
    public function show($storyId, \Swift_Mailer $mailer): Response
    {
        $repo = $this->getDoctrine()->getRepository(Inspiration::class);
        $story = $repo->find($storyId);

        $repo = $this->getDoctrine()->getRepository(Parameters::class);
        $result = $repo->findOneBy([
            'idUser' => $this->getUser()->getIdUser(),
        ]);
        if (is_null($result)) {
            $canCountView = false;
        } else {
            $canCountView = $result->getViewCounter();
        }

        if ($this->getUser()->getIdUser() != $story->getIdUser()->getIdUser() && $this->getUser()->getIdUser() != 1 && $canCountView) {

            $view = new View();
            $view->setIdUser($this->getUser());
            $view->setIdStory($story);
            $view->setDate(new \DateTime('now', new \DateTimeZone('Europe/Paris')));
            // dd($view);
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($view);
            $entityManager->flush();

            // Envoie de l'email
            $userName = $this->getUser()->getNom()." ".$this->getUser()->getPrenom() ;
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
            "story" => $story
        ]);
    }

    /**
     * @Route("/my-space/inspiration/edit/{storyId}", name="edit_inspiration")
     */
    public function edit($storyId, Request $request, \Swift_Mailer $mailer, InspirationRepository $inspirationRepository)
    {
        $repo = $this->getDoctrine()->getRepository(Inspiration::class);
        $story = $repo->find($storyId);

        if ($this->getUser()->getIdUser() != $story->getIdUser()->getIdUser()) {
            return $this->denyAccessUnlessGranted('ROLE_EDIT', $story, 'Vous n\'avez pas le droit de consulter cette story.');
        }

        $form = $this->createForm(StoryType::class, $story);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($story);
            $entityManager->flush();

            $story = $inspirationRepository->findByTitle($story->getTitle());

            if ($story->getStatut() == "public") {
                $toEmail = ["lazarefortune@gmail.com", "jessyjess00021@gmail.com", "jessicatemba.s@gmail.com"];
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

            return $this->redirectToRoute('inspiration');
        }

        return $this->render('admin/story/edit.html.twig', [
            'formStory' => $form->createView(),
            "story" => $story
        ]);
    }

    /**
     * @Route("/my-space/inspiration/delete/{storyId}", name="delete_inspiration")
     */
    public function delete($storyId, EntityManagerInterface $entityManager): Response
    {
        $story = $entityManager->getRepository(Inspiration::class)->find($storyId);
        // dd($story);
        $entityManager->remove($story);
        $entityManager->flush();
        $this->addFlash('success', 'La story a été supprimé avec succès');
        return $this->redirectToRoute('my_inspiration');
    }

    /**
     * @Route("/my-space/inspiration/views", name="views")
     */
    public function show_view(ViewRepository $viewRepository): Response
    {
        $views = $viewRepository->findAll();

        return $this->render('admin/story/views.html.twig', [
            "views" => $views
        ]);
    }

    /**
     * @Route("/my-space/inspiration/views/edit", name="views_edit")
     */
    public function edit_view(ViewRepository $viewRepository, ParametersRepository $parametersRepository): Response
    {
        $views = $viewRepository->findAll();

        $params = $parametersRepository->findAll();

        return $this->render('admin/story/views_edit.html.twig', [
            "views" => $views,
            "paramsUsers" => $params,
        ]);
    }

    /**
     * @Route("/my-space/inspiration/views/change_param/{idParam}", name="change_view_param")
     */
    public function change_view_param($idParam, ViewRepository $viewRepository, ParametersRepository $parametersRepository): Response
    {
        $param = $parametersRepository->findOneBy([
            'id' => $idParam,
        ]);

        if ( is_null($param) ) {
            $this->addFlash('danger', 'Pas de paramètres connu');
            return $this->redirectToRoute('views_edit');
        }

        $param->setViewCounter(!$param->getViewCounter());

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($param);
        $entityManager->flush();

        $this->addFlash('success', 'Paramèttres mis à jour avec succès');
        return $this->redirectToRoute('views_edit');
    }
}
