<?php

namespace App\Controller\Admin;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


class AdminController extends AbstractController
{
    
    public function index(): Response
    {

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