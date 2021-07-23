<?php

namespace App\Controller\Auth;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends AbstractController
{
    public function login(): Response{

        return $this->render('auth/login.html.twig');
    }

    public function password_reset_form(): Response{

        return $this->render('auth/password/reset_form.html.twig');
    }
}
