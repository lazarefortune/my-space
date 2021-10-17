<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Entity\Filiere;
use App\Entity\Module;
use App\Entity\Promotion;
use App\Form\PromotionType;
use App\Services\GrantedService;
use App\Repository\UserRepository;
use App\Repository\PromotionRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("/admin/promotion")
 */
class PromotionController extends AbstractController
{
    /**
     * @Route("/", name="admin_promotion_index", methods={"GET"})
     */
    public function index(PromotionRepository $promotionRepository): Response
    {
        return $this->render('admin/promotion/index.html.twig', [
            'promotions' => $promotionRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="admin_promotion_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $promotion = new Promotion();
        $form = $this->createForm(PromotionType::class, $promotion);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $promotion->setCreatedAt(new \DateTime('now', new \DateTimeZone('Europe/Paris')));
            $promotion->setUpdatedAt(new \DateTime('now', new \DateTimeZone('Europe/Paris')));

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($promotion);
            $entityManager->flush();

            return $this->redirectToRoute('admin_promotion_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('admin/promotion/new.html.twig', [
            'promotion' => $promotion,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="admin_promotion_show", methods={"GET"})
     */
    public function show(Promotion $promotion): Response
    {
        $users = $promotion->getUsers();
        return $this->render('admin/promotion/show.html.twig', [
            'promotion' => $promotion,
            'users' => $users
        ]);
    }

    /**
     * @Route("/{id}/edit", name="admin_promotion_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Promotion $promotion): Response
    {
        $promotion->setUpdatedAt(new \DateTime('now', new \DateTimeZone('Europe/Paris')));
        $school = $promotion->getSchool();
        $filieres = $school->getFilieres();

        $form = $this->createForm(PromotionType::class, $promotion);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $datas = $request->request->all();
            $userRepo = $this->getDoctrine()->getRepository(Filiere::class);
            $filiere = $userRepo->find( $datas['filiere'] );

            $promotion->setFiliere( $filiere );
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($promotion);
            $entityManager->flush();

            return $this->redirectToRoute('admin_promotion_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('admin/promotion/edit.html.twig', [
            'promotion' => $promotion,
            'filieres' => $filieres,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="admin_promotion_delete", methods={"POST"})
     */
    public function delete(Request $request, Promotion $promotion): Response
    {
        $school = $promotion->getSchool();
        if ($this->isCsrfTokenValid('delete'.$promotion->getId(), $request->request->get('_token'))) {
            $students = $promotion->getUsers();
            foreach ($students as $student) {
                $promotion->removeUser( $student );
            }

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($promotion);
            $entityManager->flush();

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($promotion);
            $entityManager->flush();
        }

        return $this->redirectToRoute('admin_school_show', [
            'id' => $school->getId()
        ], Response::HTTP_SEE_OTHER);
        // return $this->redirectToRoute('admin_promotion_index', [], Response::HTTP_SEE_OTHER);
    }

    /**
     * @Route("/student/new/{id}", name="admin_promotion_student_new", methods={"GET","POST"})
     */
    public function newStudent(Request $request, Promotion $promotion, UserRepository $usersRepo, GrantedService $grantedService): Response
    {


        $school = $promotion->getSchool();
        $allUsers = $school->getStudents();
        $users = [];
        foreach ($allUsers as $user) {
            $dontHavePromo = empty($user->getPromo());
            $haveSamePromo = (!empty($user->getPromo()) && $user->getPromo() == $promotion);
            if ( $haveSamePromo || $dontHavePromo ) {
                $users[] = $user;
            }
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
                    $promotion->addUser( $user );
                }else{
                    $promotion->removeUser( $user );
                }
                // $user->setSchool( $school );
                // $em->persist($user);
                $em->persist($promotion);
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
            return $this->redirectToRoute('admin_promotion_show', [
                'id' => $promotion->getId()
            ], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('admin/promotion/newStudent.html.twig', [
            'promotion' => $promotion,
            'users' => $users
        ]);
    }


    
}
