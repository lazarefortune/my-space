<?php

namespace App\Controller\Auth;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends AbstractController
{
    public function login(): Response{

        return $this->render('auth/login.html.twig');
    }
}
