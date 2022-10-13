<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PortfolioController extends AbstractController
{
    /**
     * @Route("/cv/{urlProfile}", name="portfolio")
     */
    public function index( $urlProfile , UserRepository $repository): Response
    {
        // Search user by urlProfile
        $user = $repository->findOneBy(['login' => $urlProfile]);
        // If user not found, redirect to 404 page
        if ( !$user ) {
//            return $this->redirectToRoute('404');
            throw $this->createNotFoundException('This user does not exist');
        }
        // If user found, display his portfolio
        return $this->render('portfolio/index.html.twig', [
            'user' => $user,
            'controller_name' => 'PortfolioController',
        ]);
    }
}
