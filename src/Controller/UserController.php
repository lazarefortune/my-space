<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Entity\Parameters;
use App\Services\SendMailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * @Security("is_granted('ROLE_USER')")
 */
class UserController extends AbstractController
{
    private function isPasswordValid(User $eval, string $plainPassword, string $password): bool
    {
        $encoder = $this->encoderFactory->getEncoder($eval);

        return $encoder->isPasswordValid($password, $plainPassword, null);
    }

    /**
     * @Route("/profil", name="account")
     */
    public  function show()
    {
        return $this->render('user/index.html.twig');
    }

    /**
     * @Route("/profil/edition", name="account_edit")
     *
     * @param Request $request
     * @param UserPasswordEncoderInterface $encoder
     * @return void
     */
    public function edit(Request $request, UserPasswordEncoderInterface $encoder)
    {

        $user = $this->getUser();

        $form = $this->createForm(UserType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
    
                $today = new \DateTime('now', new \DateTimeZone('Europe/Paris'));
                $user->setUpdatedAt( $today );

                
                $image = $request->files->get('user')['profile_picture'];
                if (isset($image)) {    
                    
                    // $fichier = md5(uniqid()).'.'. $image->guessExtension();
                    $fichier = str_replace( " ", "-", $user->getNom().'-'.$user->getPrenom() ) . '-'. md5(uniqid()) .'.'. $image->guessExtension();
                   // Supprimer l'ancienne image
                    if ( !empty( $user->getProfilePicture() ) && file_exists( $this->getParameter('user_profile_directory').'/'.$user->getProfilePicture() ) )
                    {
                        unlink($this->getParameter('user_profile_directory').'/'.$user->getProfilePicture());
                    }
                    $image->move(
                        $this->getParameter('user_profile_directory'),
                        $fichier
                    );

                    $user->setProfilePicture($fichier);
                }


                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($user);
                $entityManager->flush();
                $this->addFlash('success', 'Profil mis à jour avec succès');

            return $this->redirectToRoute('account_edit');
        }

        return $this->render('user/edit.html.twig', [
            'formUser' => $form->createView()
        ]);
    }

    /**
     * @Route("/profil/photo/suppression", name="account_delete_photo")
     */
    public function deletePhoto()
    {
        $user = $this->getUser();
        
        if ( !empty( $user->getProfilePicture() ) && file_exists( $this->getParameter('user_profile_directory').'/'.$user->getProfilePicture() ) )
        {
            unlink($this->getParameter('user_profile_directory').'/'.$user->getProfilePicture());
        }

        $user->setProfilePicture(null);

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($user);
        $entityManager->flush();

        $this->addFlash('success', 'Photo supprimée avec succès');

        return $this->redirectToRoute('account_edit');
    }

    /**
     * @Route("/profil/edition/mot-de-passe", name="account_edit_password")
     *
     * @param Request $request
     * @param UserPasswordEncoderInterface $encoder
     * @return void
     */
    public function editPassword(Request $request, UserPasswordEncoderInterface $encoder)
    {
        $user = $this->getUser();
        
        if ( $request->isMethod('POST') ) {

            $match = $encoder->isPasswordValid($user, $request->request->get('oldPassword'));

            if ($match) {   
                if ( $request->request->get('newPassword') != $request->request->get('newPasswordConfirm') ) {
                    $this->addFlash('danger', 'Les mots de passe ne correspondent pas');
                } else {
                    $passwordEncoded = $encoder->encodePassword($user, $request->request->get('newPassword'));
                    $user->setPassword($passwordEncoded);

                    $this->addFlash('success', 'Mot de passe modifié succès');
                }
            } else {
                $this->addFlash('danger', 'Mot de passe actuel incorrect');
            }
                
            $today = new \DateTime('now', new \DateTimeZone('Europe/Paris'));
            $user->setUpdatedAt( $today );
            
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('account_edit_password');
        }

        return $this->render('user/editPassword.html.twig');
    }

    /**
     * @Route("/profil/suppression", name="account_delete")
     */
    public function delete( EntityManagerInterface $entityManager, SendMailService $mailer )
    {
        $currentUserId = $this->getUser()->getIdUser();
        // if ($currentUserId == $id) {
        //     $session = $this->get('session');
        //     $session = new Session();
        //     $session->invalidate();
        // }
        $id = $this->getUser()->getIdUser();

        if ( $id == 1 ) {
            $this->addFlash('danger', 'Vous ne pouvez pas supprimer le compte administrateur');
            return $this->redirectToRoute('account');
        }

        // $user = $entityManager->getRepository(User::class)->find($id);

        $user = $this->getUser();

        try {
            $mailer->sendMail( 
                $this->getParameter('send_mail_user'),
                [ $user->getEmail() ],
                'Suppression de votre compte',
                'user/delete_account',
                [
                    'user' => $user
                ]
            );
        } catch (\Throwable $th) {
            $this->addFlash( 'danger', 'Il y a eu une erreur lors de l\'envoie du mail' );
        }
        
        // $this->container->get('security.token_storage')->setToken(null);

        $user->setIsDeleted( true );
        $entityManager->persist($user);
        $entityManager->flush();

        // // Suppression des données de l'utilisateur
        // $parameters = $entityManager->getRepository(Parameters::class)->findBy(
        //     [
        //         'idUser' => $id,
        //     ]
        // );

        // foreach ($parameters as $parameter) {
        //     $entityManager->remove($parameter);
        // }
        
        // // Suppression de l'utilisateur
        // $entityManager->remove($user);
        // $entityManager->flush();

        $this->addFlash('success', 'Votre compte utilisateur a bien été désactivé, il sera supprimé dans 15 jours!');
        return $this->redirectToRoute('account');
        // return $this->redirectToRoute('app_register');
    }

    /**
     * @Route("/verify-email/send", name="send_verify_email")
     */
    public function sendEmailVerify( SendMailService $mailer )
    {
        $user = $this->getUser();

        $user->setActivationToken(md5(uniqid()));

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($user);
        $entityManager->flush();

        $mailer->sendMail( 
            $this->getParameter('send_mail_user'),
            [ $user->getEmail() ],
            'Confirmation de votre compte',
            'activation_verify_email',
            [
                'user' => $user,
                'token' => $user->getActivationToken()
            ]
        );
        
        $this->addFlash( 'success', 'Email envoyé avec succès, veuillez consulter votre messagerie.');
        return $this->redirectToRoute('account');
    }
}
