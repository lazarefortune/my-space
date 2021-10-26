<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Form\EditUserType;
use App\Services\GrantedService;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;

/**
 * @Route("/admin/users",name="admin_users_")
 * @Security("is_granted('ROLE_ADMIN') and is_granted('ROLE_USER')")
 */
class UserController extends AbstractController
{
    /**
     * @Route("/list" , name="list")
     */
    public function list( UserRepository $usersRepo, GrantedService $grantedService, Request $request )
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
                if ( $user->getIdUser() != $this->getUser()->getIdUser() ) $users[] = $user;
            }
        }
       

        // On vérifie si on a une requête Ajax
        if ( $request->get('ajax') ) {
            return new JsonResponse([
                'content' => $this->renderView('admin/users/index.html.twig', compact('users'))
            ]);
        }

        if ( $request->isMethod( 'post' ) ) {
            $datas = $request->request->all();
            $users = [];
            $userRepo = $this->getDoctrine()->getRepository(User::class);
            $em = $this->getDoctrine()->getManager();
            foreach ($datas as $data) {
                $user = $userRepo->find( $data );
                $user->setIsVerified( !$user->isVerified() );
                $em->persist($user);
                $users[] = $user;
            }
            $em->flush();
            // dump( $users );
            // dd( $request->request->all() );
            $this->addFlash( 'success', 'mise à jour effectuée' );
            $this->redirectToRoute( 'admin_users_list' );
        }

        return $this->render('admin/users/index.html.twig', [
            'users' => $users
        ]);
    }

    /**
     * @Route("/edit/{id}", name="edit")
     *
     * @param User $user
     * @param Request $request
     * @return void
     */
    public function editUser( User $user, Request $request )
    {
        $form = $this->createForm( EditUserType::class, $user );
        $form->handleRequest( $request );

        if ( $form->isSubmitted() && $form->isValid() ) {
            $em = $this->getDoctrine()->getManager();
            $em->persist( $user );
            $em->flush();

            $this->addFlash( 'success', 'Utilisateur modifié avec succès' );
            return $this->redirectToRoute( 'admin_users_list' );
        }

        return $this->render('admin/users/edit.html.twig', [
            'userForm' => $form->createView()
        ]);
    }
    
}
