<?php

namespace App\Controller\Admin;

use App\Repository\ViewRepository;
use App\Repository\ParametersRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("/admin/view",name="admin_views_")
 */
class ViewController extends AbstractController
{
    /**
     * @Route("/liste", name="index")
     */
    public function index(ViewRepository $viewRepository): Response
    {
        $views = $viewRepository->findBy([],[
            'date' => 'DESC'
        ]);

        return $this->render('admin/view/index.html.twig', [
            "views" => $views
        ]);
    }

    /**
     * @Route("/modification", name="edit")
     */
    public function edit_view(ViewRepository $viewRepository, ParametersRepository $parametersRepository): Response
    {
        $views = $viewRepository->findAll();

        $params = $parametersRepository->findAll();

        return $this->render('admin/view/views_edit.html.twig', [
            "views" => $views,
            "paramsUsers" => $params,
        ]);
    }

    /**
     * @Route("/changer_parametre/{idParam}", name="change_param")
     */
    public function change_view_param($idParam, ParametersRepository $parametersRepository): Response
    {
        $param = $parametersRepository->findOneBy([
            'id' => $idParam,
        ]);

        if ( is_null($param) ) {
            $this->addFlash('danger', 'Pas de paramètres connu');
            return $this->redirectToRoute('admin_views_edit');
        }

        $param->setViewCounter(!$param->getViewCounter());

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($param);
        $entityManager->flush();

        $this->addFlash('success', 'Paramèttres mis à jour avec succès');
        return $this->redirectToRoute('admin_views_edit');
    }
}
