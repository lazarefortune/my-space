<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class MaintenanceController extends AbstractController
{
    public function index(): Response{

        return $this->render('bundles/TwigBundle/Exception/maintenance.html.twig');
    }
}
