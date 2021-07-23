<?php

namespace App\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class EmailController extends AbstractController
{
    public function index(): Response
    {

        return $this->render('admin/email.html.twig');
    }
}
