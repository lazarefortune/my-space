<?php

namespace App\Controller\Admin;

use App\Entity\Matiere;
use App\Form\MatiereType;
use App\Form\ModuleMatiereType;
use App\Repository\MatiereRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("/admin/matiere")
 */
class MatiereController extends AbstractController
{
    /**
     * @Route("/", name="admin_matiere_index", methods={"GET"})
     */
    public function index(MatiereRepository $matiereRepository): Response
    {
        return $this->render('admin/matiere/index.html.twig', [
            'matieres' => $matiereRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="admin_matiere_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $matiere = new Matiere();
        $form = $this->createForm(MatiereType::class, $matiere);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($matiere);
            $entityManager->flush();

            return $this->redirectToRoute('admin_matiere_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('admin/matiere/new.html.twig', [
            'matiere' => $matiere,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="admin_matiere_show", methods={"GET"})
     */
    public function show(Matiere $matiere): Response
    {
        return $this->render('admin/matiere/show.html.twig', [
            'matiere' => $matiere,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="admin_matiere_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Matiere $matiere): Response
    {
        $form = $this->createForm(ModuleMatiereType::class, $matiere);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $module = $matiere->getModule();
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('admin_module_show', [
                'id' => $module->getId()
            ], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('admin/matiere/edit.html.twig', [
            'matiere' => $matiere,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="admin_matiere_delete", methods={"POST"})
     */
    public function delete(Request $request, Matiere $matiere): Response
    {
        if ($this->isCsrfTokenValid('delete'.$matiere->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($matiere);
            $entityManager->flush();
        }

        return $this->redirectToRoute('admin_matiere_index', [], Response::HTTP_SEE_OTHER);
    }
}
