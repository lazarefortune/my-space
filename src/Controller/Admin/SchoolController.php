<?php

namespace App\Controller\Admin;

use App\Entity\Filiere;
use App\Entity\User;
use App\Entity\School;
use App\Form\SchoolType;
use App\Entity\Promotion;
use App\Form\SchoolFiliereType;
use App\Services\GrantedService;
use App\Form\SchoolPromotionType;
use App\Repository\UserRepository;
use App\Repository\SchoolRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("/admin/school")
 */
class SchoolController extends AbstractController
{
    /**
     * @Route("/", name="admin_school_index", methods={"GET"})
     */
    public function index(SchoolRepository $schoolRepository): Response
    {
        return $this->render('admin/school/index.html.twig', [
            'schools' => $schoolRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="admin_school_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $school = new School();
        $form = $this->createForm(SchoolType::class, $school);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($school);
            $entityManager->flush();

            return $this->redirectToRoute('admin_school_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('admin/school/new.html.twig', [
            'school' => $school,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="admin_school_show", methods={"GET"})
     */
    public function show(School $school): Response
    {
        return $this->render('admin/school/show.html.twig', [
            'school' => $school,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="admin_school_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, School $school): Response
    {
        $form = $this->createForm(SchoolType::class, $school);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('admin_school_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('admin/school/edit.html.twig', [
            'school' => $school,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="admin_school_delete", methods={"POST"})
     */
    public function delete(Request $request, School $school): Response
    {
        if ($this->isCsrfTokenValid('delete'.$school->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            
            

            // on supprime les promotions
            $promotions = $school->getPromotions();
            foreach ($promotions as $promotion) {
                // On supprime tous les étudiants dans chaque promo
                $students = $promotion->getUsers();
                foreach ($students as $student) {
                    $promotion->removeUser( $student );
                }

                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($promotion);
                $entityManager->flush();
                
                $school->removePromotion( $promotion );
            }

            // On supprime les étudiants inscrits dans l'etablissement
            $students = $school->getStudents();
            foreach ($students as $student) {
                $student->setSchool( null );
            }

            $entityManager->persist($school);
            $entityManager->flush();

            // On supprime les filières
            $filieres = $school->getFilieres();
            foreach ($filieres as $filiere) {
                $school->removeFiliere( $filiere );
            }

            $entityManager->persist($school);
            $entityManager->flush();

            $entityManager->remove($school);
            $entityManager->flush();
        }

        return $this->redirectToRoute('admin_school_index', [], Response::HTTP_SEE_OTHER);
    }

    /**
     * @Route("/promotion/new/{id}", name="admin_school_promotion_new", methods={"GET","POST"})
     */
    public function newPromotion(Request $request, School $school): Response
    {
        $promotion = new Promotion();
        
        $filieres = $school->getFilieres();
        $form = $this->createForm(SchoolPromotionType::class, $promotion);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $datas = $request->request->all();
            $userRepo = $this->getDoctrine()->getRepository(Filiere::class);
            $filiere = $userRepo->find( $datas['filiere'] );

            $promotion->setFiliere( $filiere );
            $promotion->setSchool( $school );
            $promotion->setCreatedAt(new \DateTime('now', new \DateTimeZone('Europe/Paris')));
            $promotion->setUpdatedAt(new \DateTime('now', new \DateTimeZone('Europe/Paris')));

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($promotion);
            $entityManager->flush();

            return $this->redirectToRoute('admin_school_show', [
                'id' => $school->getId()
            ], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('admin/promotion/new.html.twig', [
            'filieres' => $filieres,
            'promotion' => $promotion,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/filiere/new/{id}", name="admin_school_filiere_new", methods={"GET","POST"})
     */
    public function newFiliere(Request $request, School $school): Response
    {
        $filiere = new Filiere();
        $form = $this->createForm(SchoolFiliereType::class, $filiere);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            
            $filiere->setSchool( $school );

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($filiere);
            $entityManager->flush();

            return $this->redirectToRoute('admin_school_show', [
                'id' => $school->getId()
            ], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('admin/filiere/new.html.twig', [
            'filiere' => $filiere,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/student/new/{id}", name="admin_school_student_new", methods={"GET","POST"})
     */
    public function newStudent(Request $request, School $school, UserRepository $usersRepo, GrantedService $grantedService): Response
    {

        $roles = [ "ROLE_MEMBER" ];

        // On vérifie si on a une requête Ajax
        if ( $request->get('ajax') ) {
            $role = $request->get('role');
            $roles = [$role];
        }

        // On filtre les utilisateurs
        /*$users = $usersRepo->findByRole( "ADMIN" );*/
        $allUsers = $usersRepo->findAll();
        $users = [];
        foreach ($allUsers as $user) {
            if ( $grantedService->isGranted( $user, $roles ) ) {
                // if ( ($user->getIdUser() != $this->getUser()->getIdUser() ) ){
                    if( (!empty($user->getSchool()) && $user->getSchool() == $school) || empty( $user->getSchool() ) ) {
                        # code...
                        $users[] = $user;
                    }
                // } 
            }
        }
       

        // On vérifie si on a une requête Ajax
        if ( $request->get('ajax') ) {
            return new JsonResponse([
                'content' => $this->renderView('admin/school/newStudent.html.twig', compact('users', 'school'))
            ]);
        }

        if ( $request->isMethod( 'post' ) ) {
            $datas = $request->request->all();
            $action = $datas["action"];
            unset( $datas["action"] );

            $users = [];
            $userRepo = $this->getDoctrine()->getRepository(User::class);
            $em = $this->getDoctrine()->getManager();
            foreach ($datas as $data) {
                $user = $userRepo->find( $data );
                if ( $action == "suscribe" ) {
                    $school->addStudent( $user );
                }else{
                    $user->setPromo( null );
                    $school->removeStudent( $user );
                }
                // $user->setSchool( $school );
                // $em->persist($user);
                $em->persist($school);
                $users[] = $user;
            }
            $em->flush();
            // dump( $users );
            // dd( $request->request->all() );
            if ( $action == "suscribe" ) {
                $this->addFlash( 'success', 'Inscription réussie' );
            }else{
                $this->addFlash( 'warning', 'Désiscription réussie' );
            }
            return $this->redirectToRoute('admin_school_show', [
                'id' => $school->getId()
            ], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('admin/school/newStudent.html.twig', [
            'school' => $school,
            'users' => $users
        ]);
    }
}
