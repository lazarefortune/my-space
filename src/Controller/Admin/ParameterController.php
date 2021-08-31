<?php

namespace App\Controller\Admin;

use App\Entity\Parameters;
use App\Repository\ViewRepository;
use App\Repository\ParametersRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("/admin/users/parameters", name="admin_users_parameters_")
 */
class ParameterController extends AbstractController
{
    /**
     * @Route("/listes" , name="index" )
     */
    public function index(ViewRepository $viewRepository, ParametersRepository $parametersRepository): Response
    {
        $views = $viewRepository->findAll();

        $params = $parametersRepository->findAll();

        return $this->render('admin/parameter/index.html.twig', [
            "views" => $views,
            "paramsUsers" => $params,
        ]);
    }

    /**
     * @Route("/change/notification/view/{id}" , name="change_notif_view")
     */
    public function change_statut_notif_view( Parameters $parameters ) : Response
    {
        // dd( $parameters );
        $parameters->setViewCounter( ($parameters->getViewCounter()) ? false : true );
        $em = $this->getDoctrine()->getManager();
        $em->persist( $parameters );
        $em->flush();

        return new Response( "true" );
    }

    /**
     * @Route("/change/notification/email/{id}" , name="change_notif_email")
     */
    public function change_statut_notif_email( Parameters $parameters ) : Response
    {
        // dd( $parameters );
        $parameters->setEmailNotifications( ($parameters->getEmailNotifications()) ? false : true );
        $em = $this->getDoctrine()->getManager();
        $em->persist( $parameters );
        $em->flush();

        return new Response( "true" );
    }
}
