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
     * @Route("/inspiration", name="inspiration")
     */
    public function index(InspirationRepository $inspirationRepository): Response
    {

        $allStory = $inspirationRepository->findAll();
        return $this->render('admin/story/inspiration.html.twig', [
            'allStory' => $allStory,
        ]);
    }

    /**
     * @Route("/inspiration/create", name="create_inspiration")
     */
    public function create(Request $request): Response
    {
        $story = new Inspiration();

        $form = $this->createForm(StoryType::class, $story);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($story);
            $entityManager->flush();

            $this->addFlash('success', 'Profil mis à jour avec succès');

            return $this->redirectToRoute('inspiration');
        }

        return $this->render('admin/story/create.html.twig', [
            'formStory' => $form->createView()
        ]);
    }

    /**
     * @Route("/inspiration/show/{storyId}", name="show_inspiration")
     */
    public function show($storyId): Response
    {
        $repo = $this->getDoctrine()->getRepository(Inspiration::class);
        $story = $repo->find($storyId);
        // dd($story);

        return $this->render('admin/story/show.html.twig', [
            "story" => $story
        ]);
    }
}
