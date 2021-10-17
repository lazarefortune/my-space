<?php

namespace App\Controller\Admin;

use App\Entity\Module;
use App\Entity\Matiere;
use App\Form\ModuleType;
use App\Form\FiliereModuleType;
use App\Form\ModuleMatiereType;
use App\Repository\ModuleRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("/admin/module")
 */
class ModuleController extends AbstractController
{
    /**
     * @Route("/", name="admin_module_index", methods={"GET"})
     */
    public function index(ModuleRepository $moduleRepository): Response
    {
        return $this->render('admin/module/index.html.twig', [
            'modules' => $moduleRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="admin_module_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $module = new Module();
        $form = $this->createForm(ModuleType::class, $module);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($module);
            $entityManager->flush();

            return $this->redirectToRoute('admin_module_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('admin/module/new.html.twig', [
            'module' => $module,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="admin_module_show", methods={"GET"})
     */
    public function show(Module $module): Response
    {
        return $this->render('admin/module/show.html.twig', [
            'module' => $module,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="admin_module_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Module $module): Response
    {
        $form = $this->createForm(FiliereModuleType::class, $module);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();
            $filiere = $module->getFiliere();
            return $this->redirectToRoute('admin_filiere_show', [
                'id' => $filiere->getId()
            ], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('admin/module/edit.html.twig', [
            'module' => $module,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="admin_module_delete", methods={"POST"})
     */
    public function delete(Request $request, Module $module): Response
    {
        if ($this->isCsrfTokenValid('delete'.$module->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($module);
            $entityManager->flush();
        }

        return $this->redirectToRoute('admin_module_index', [], Response::HTTP_SEE_OTHER);
    }


    /**
     * @Route("/matiere/new/{id}", name="admin_module_matiere_new", methods={"GET","POST"})
     */
    public function newMatiere(Request $request, Module $module): Response
    {
        $matiere = new Matiere();
        $form = $this->createForm(ModuleMatiereType::class, $matiere);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $matiere->setModule( $module );
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($matiere);
            $entityManager->flush();

            return $this->redirectToRoute('admin_module_show', [
                'id' => $module->getId()
            ], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('admin/matiere/new.html.twig', [
            'matiere' => $matiere,
            'form' => $form,
        ]);
    }
}
