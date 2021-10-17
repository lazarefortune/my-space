<?php

namespace App\Controller\School;

use App\Entity\Course;
use App\Entity\Matiere;
use App\Form\CourseType;
use App\Repository\CourseRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/school/course")
 */
class CourseController extends AbstractController
{
    /**
     * @Route("/", name="school_course_index", methods={"GET"})
     */
    public function index(CourseRepository $courseRepository): Response
    {
        return $this->render('school/course/index.html.twig', [
            'courses' => $courseRepository->findAll(),
        ]);
    }

    /**
     * @Route("/matiere/{id}", name="school_matiere_course_index", methods={"GET"})
     */
    public function indexMatiere(CourseRepository $courseRepository, Matiere $matiere): Response
    {
        $courses = $courseRepository->findBy([
            'matiere' => $matiere->getId(),
            'user' => $this->getUser()->getIdUser()
        ]);
        // $courses = $matiere->getCourses();
        return $this->render('school/course/index.html.twig', [
            'courses' => $courses,
            'matiere' => $matiere
            // 'courses' => $courseRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="school_course_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $course = new Course();
        $form = $this->createForm(CourseType::class, $course);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($course);
            $entityManager->flush();

            return $this->redirectToRoute('school_course_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('school/course/new.html.twig', [
            'course' => $course,
            'form' => $form,
        ]);
    }
    
    /**
     * @Route("/new/{id}", name="school_matiere_course_new", methods={"GET","POST"})
     */
    public function newCourse(Request $request, Matiere $matiere): Response
    {
        $course = new Course();
        $form = $this->createForm(CourseType::class, $course);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $course->setMatiere( $matiere );
            $course->setUser( $this->getUser() );
            $course->setCreatedAt(new \DateTime('now', new \DateTimeZone('Europe/Paris')));
            $course->setUpdatedAt(new \DateTime('now', new \DateTimeZone('Europe/Paris')));

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($course);
            $entityManager->flush();

            return $this->redirectToRoute('school_matiere_course_index', [
                'id' => $matiere->getId()
            ], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('school/course/new.html.twig', [
            'course' => $course,
            'matiere' => $matiere,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="school_course_show", methods={"GET"})
     */
    public function show(Course $course): Response
    {
        return $this->render('school/course/show.html.twig', [
            'course' => $course,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="school_course_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Course $course): Response
    {        
        $form = $this->createForm(CourseType::class, $course);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $course->setUpdatedAt(new \DateTime('now', new \DateTimeZone('Europe/Paris')));
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($course);
            $entityManager->flush();

            return $this->redirectToRoute('school_course_show', [
                'id' => $course->getId()
            ], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('school/course/edit.html.twig', [
            'course' => $course,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="school_course_delete", methods={"POST"})
     */
    public function delete(Request $request, Course $course): Response
    {
        $matiere = $course->getMatiere();
        if ($this->isCsrfTokenValid('delete'.$course->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($course);
            $entityManager->flush();
        }

        return $this->redirectToRoute('school_matiere_course_index', [
            'id' => $matiere->getId()
        ], Response::HTTP_SEE_OTHER);
    }
}
