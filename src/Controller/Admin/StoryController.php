<?php

namespace App\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class StoryController extends AbstractController
{
    /**
     * @Route("/inspiration", name="inspiration")
     */
    public function index(): Response
    {

        return $this->render('admin/inspiration.html.twig');
    }

}