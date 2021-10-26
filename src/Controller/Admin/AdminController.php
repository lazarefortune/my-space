<?php

namespace App\Controller\Admin;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @isGranted("ROLE_ADMIN")
 * @Route("/admin" , name="admin_")
 */
class AdminController extends AbstractController
{
    /**
     * @Route("/" , name="home")
     *
     * @return Response
     */
    public function index( Request $request ): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        // $id = $this->container->get('request_stack')->getCurrentRequest()->getClientIp();
        // $ip = $_SERVER;
        // $hostname = gethostbyaddr($_SERVER['REMOTE_ADDR']);
        // dd($hostname);
        // dd($ip);
        // dd($id);
        return $this->render('admin/index.html.twig');
    }

    /**
     * @Route("/test" , name="test")
     */
    public function test() : Response
    {
        return $this->render('test.html.twig');
    }
    
    /**
     * @Route("/test2" , name="test2")
     */
    public function test2() : Response
    {
        return $this->render('test2.html.twig');
    }

}