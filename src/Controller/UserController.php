<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

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

        // $user =  new User();


        $user = $this->getUser();

        // dd($user);
        // $user->setNomEval();
        // $form = $this->createFormBuilder($user)
        //             ->add('nomEval')
        //             // ->add('prenomEval', TextType::class)
        //             // ->add('email', EmailType::class, [
        //             //     'required' => "false"
        //             // ])
        //             // ->add('password', PasswordType::class)
        //             // ->add('update', SubmitType::class, ['label' => 'Mettre à jour'])
        //             ->getForm();

        $form = $this->createForm(UserType::class, $user);

        $form->handleRequest($request);

        // dump($user);

        if ($form->isSubmitted() && $form->isValid()) {

            $match = $encoder->isPasswordValid($user, $request->request->get('oldPassword'));

            if ($match) {
                if ($request->request->get('newPassword')) {
                    $passwordEncoded = $encoder->encodePassword($user, $request->request->get('newPassword'));
                    $user->setPassword($passwordEncoded);
                }

                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($user);
                $entityManager->flush();

                $this->addFlash('success', 'Profil mis à jour avec succès');
            } else {
                $this->addFlash('danger', 'Mot de passe actuel incorrect');
            }

            return $this->redirectToRoute('account_edit');
        }

        return $this->render('user/edit.html.twig', [
            'formUser' => $form->createView()
        ]);
    }

    /**
     * @Route("/user/delete/{id}" , name="delete_account")
     *
     * 
     */
    public function delete($id, EntityManagerInterface $entityManager, \Swift_Mailer $mailer)
    {
        $currentUserId = $this->getUser()->getIdUser();
        // if ($currentUserId == $id) {
        //     $session = $this->get('session');
        //     $session = new Session();
        //     $session->invalidate();
        // }
        $user = $entityManager->getRepository(User::class)->find($id);

        $message = (new \Swift_Message('Suppression de votre compte'))
            ->setFrom('service@lazarefortune.com')
            ->setTo($user->getEmail())
            ->setBody(
                $this->renderView(
                    'emails/delete_account.html.twig', [
                        'nom' => $user->getNom()." ".$user->getPrenom()
                    ]
                ),
                'text/html'
            );
        $mailer->send($message);
        
        // $user = $this->getUser();
        $this->container->get('security.token_storage')->setToken(null);

        $entityManager->remove($user);
        $entityManager->flush();

        $this->addFlash('success', 'Votre compte utilisateur a bien été supprimé !');

        return $this->redirectToRoute('app_register');
    }

    /**
     * @Route("/verify-email/send", name="send_verify_email")
     */
    public function sendEmailVerify(\Swift_Mailer $mailer)
    {
        $user = $this->getUser();
        // dd($user->getActivationToken());

        $user->setActivationToken(md5(uniqid()));

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($user);
        $entityManager->flush();

        $message = (new \Swift_Message('Activation de votre compte'))
            ->setFrom('service@lazarefortune.com')
            ->setTo($user->getEmail())
            ->setBody(
                $this->renderView(
                    'emails/activation_email.html.twig',
                    [
                        'token' => $user->getActivationToken()
                    ]
                ),
                'text/html'
            );
        $mailer->send($message);
        
        $this->addFlash( 'success', 'Email envoyé avec succès');
        return $this->redirectToRoute('account');
    }
}
