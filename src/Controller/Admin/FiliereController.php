<?php

namespace App\Controller\Admin;

use App\Entity\Module;
use App\Entity\Filiere;
use App\Form\FiliereType;
use App\Form\FiliereModuleType;
use App\Repository\FiliereRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("/admin/filiere")
 */
class FiliereController extends AbstractController
{
    /**
     * @Route("/", name="admin_filiere_index", methods={"GET"})
     */
    public function index(FiliereRepository $filiereRepository): Response
    {
        return $this->render('admin/filiere/index.html.twig', [
            'filieres' => $filiereRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="admin_filiere_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $filiere = new Filiere();
        $form = $this->createForm(FiliereType::class, $filiere);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($filiere);
            $entityManager->flush();

            return $this->redirectToRoute('admin_filiere_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('admin/filiere/new.html.twig', [
            'filiere' => $filiere,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="admin_filiere_show", methods={"GET"})
     */
    public function show(Filiere $filiere): Response
    {
        return $this->render('admin/filiere/show.html.twig', [
            'filiere' => $filiere,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="admin_filiere_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Filiere $filiere): Response
    {
        $form = $this->createForm(FiliereType::class, $filiere);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            // return $this->redirectToRoute('admin_filiere_index', [], Response::HTTP_SEE_OTHER);
            return $this->redirectToRoute('admin_school_show', [
                'id' => $filiere->getSchool()->getId()
            ], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('admin/filiere/edit.html.twig', [
            'filiere' => $filiere,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="admin_filiere_delete", methods={"POST"})
     */
    public function delete(Request $request, Filiere $filiere): Response
    {
        $school = $filiere->getSchool();
        if ($this->isCsrfTokenValid('delete'.$filiere->getId(), $request->request->get('_token'))) {
            
            // on supprime les promotions
            $promotions = $filiere->getPromotions();
            foreach ($promotions as $promotion) {
                // On supprime tous les étudiants dans chaque promo
                $students = $promotion->getUsers();
                foreach ($students as $student) {
                    $promotion->removeUser( $student );
                }

                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($promotion);
                $entityManager->flush();
                // On supprime la promotion de la filière
                $filiere->removePromotion( $promotion );
            }
            
            $entityManager = $this->getDoctrine()->getManager();
            // On enregistre la filiere (sans promo)
            $entityManager->persist($filiere);
            $entityManager->flush();
            // On supprime la filiere
            $entityManager->remove($filiere);
            $entityManager->flush();
        }

        return $this->redirectToRoute('admin_school_show', [
            'id' => $school->getId()
        ], Response::HTTP_SEE_OTHER);
        // return $this->redirectToRoute('admin_filiere_index', [], Response::HTTP_SEE_OTHER);
    }


    /**
     * @Route("/module/new/{id}", name="admin_filiere_module_new", methods={"GET","POST"})
     */
    public function newModule(Request $request, Filiere $filiere): Response
    {
        $module = new Module();
        $form = $this->createForm(FiliereModuleType::class, $module);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $module->setFiliere( $filiere );
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($module);
            $entityManager->flush();

            return $this->redirectToRoute('admin_filiere_show', [
                'id' => $filiere->getId()
            ], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('admin/module/new.html.twig', [
            'module' => $module,
            'form' => $form,
        ]);
    }
}
