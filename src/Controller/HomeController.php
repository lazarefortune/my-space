<?php

namespace App\Controller;
use Twig\Environment;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class HomeController extends AbstractController
{
    /**
     * @Route("/" , name="home")
     */
    public function index(): Response{

        return $this->render('index.html.twig');
    }

    /**
     * @Route("/email", name="email_test")
     */
    public function emailTest(): Response
    {
        return $this->render('layouts/emails/test.html.twig');
    }
}
