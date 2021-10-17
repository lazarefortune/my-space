<?php

namespace App\Controller\School;
use Twig\Environment;
use App\Repository\CourseRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("/school", name="school_")
 */
class SchoolController extends AbstractController
{
    /**
     * @Route("/" , name="home")
     */
    public function index( CourseRepository $courseRepository ): Response{
        $courses = $courseRepository->findBy([
            'user' => $this->getUser()->getIdUser()
        ]);
        return $this->render('school/index.html.twig', [
            'courses' => $courses
        ]);
    }


}
