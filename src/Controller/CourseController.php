<?php

namespace App\Controller;

use App\Entity\Course;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CourseController extends AbstractController
{
    /**
     * @Route("/cours", name="course")
     */
    public function index(): Response
    {
        $courses = $this->getDoctrine()->getRepository(Course::class)->findAll();
        return $this->render('course/index.html.twig', [
            'courses' => $courses,
        ]);
    }
}
