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
     * @Route("/", name="home_site")
     *
     * @return void
     */
    public function index_site(){
        return $this->render('public/index.html.twig');
    }

    /**
     * @Route("/home" , name="home")
     */
    public function index(): Response{

        return $this->render('index.html.twig');
    }

}
