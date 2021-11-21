<?php

namespace App\Controller;

use App\Entity\Parameters;
use App\Entity\User;
use App\Form\RegisterStep2Type;
use App\Form\RegistrationFormType;
use App\Repository\UserRepository;
use App\Security\EmailVerifier;
use App\Security\LoginFormAuthenticator;
use App\Services\SendMailService;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

class RegistrationController extends AbstractController
{
    private $emailVerifier;

    public function __construct(EmailVerifier $emailVerifier)
    {
        $this->emailVerifier = $emailVerifier;
    }

    /**
     * @Route("/inscription", name="app_register")
     */
    public function register(Request $request, UserPasswordEncoderInterface $passwordEncoder, GuardAuthenticatorHandler $guardHandler, LoginFormAuthenticator $authenticator, SendMailService $mailer): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $user->setPassword(
                $passwordEncoder->encodePassword(
                    $user,
                    $form->get('password')->getData()
                    )
                );
                
            $today = new \DateTime('now', new \DateTimeZone('Europe/Paris'));
            $user->setActivationToken(md5(uniqid()));

            $user->setCreatedAt( $today );
            $user->setUpdatedAt( $today );
            $user->setLastLogin( $today );
            
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();
            
            $userRefresh = $entityManager->getRepository(User::class)->findOneBy( [
                'login' => $user->getLogin()
            ] );

            if( $userRefresh ){

                $param = new Parameters();
                $param->setIdUser( $userRefresh );
                $entityManager->persist($param);
                $entityManager->flush();
            }

            $guardHandler->authenticateUserAndHandleSuccess(
                $user,
                $request,
                $authenticator,
                'main' // firewall name in security.yaml
            );

            $this->addFlash('success', 'Votre compte a été créé !');
            
        return $this->redirectToRoute('app_register_step_2', [
                'id' => $user->getIdUser() ,
            ]);
            // generate a signed url and email it to the user
            // $this->emailVerifier->sendEmailConfirmation('app_verify_email', $user,
            //     (new TemplatedEmail())
            //         ->from(new Address('servicefortuneindustry@gmail.com', "My Space"))
            //         ->to($user->getEmail())
            //         ->subject('Please Confirm your Email')
            //         ->htmlTemplate('registration/confirmation_email.html.twig')
            // );

            
            // do anything else you need here, like send an email

            // Information pour le mail
            $context = [
                'user' => $user,
                'token' => $user->getActivationToken()
            ];
            // envoie de l'email
            $mailer->sendMail(
                [
                    $this->getParameter('send_mail_user')
                ],
                [
                    $user->getEmail()
                ],
                'Confirmation de votre inscription',
                'activation_email',
                $context
            );

            return $guardHandler->authenticateUserAndHandleSuccess(
                $user,
                $request,
                $authenticator,
                'main' // firewall name in security.yaml
            );
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    /**
     * @Route("/inscription/etape-2/{id}", name="app_register_step_2")
     */
    public function registerStep2( Request $request , $id , GuardAuthenticatorHandler $guardHandler, LoginFormAuthenticator $authenticator, \Swift_Mailer $mailer): Response
    {
        // $user = $this->getDoctrine()->getRepository(User::class)->find($id);
        $user = $this->getUser();
        $form = $this->createForm(RegisterStep2Type::class, $user);
        $form->handleRequest($request);
        if( $form->isSubmitted() && $form->isValid() ){

            $entityManager = $this->getDoctrine()->getManager();
            $user->setUpdatedAt( new \DateTime('now', new \DateTimeZone('Europe/Paris')) );
            $entityManager->persist( $user );
            $entityManager->flush();

            // return $guardHandler->authenticateUserAndHandleSuccess(
            //     $user,
            //     $request,
            //     $authenticator,
            //     'main' // firewall name in security.yaml
            // );

            return $this->redirectToRoute('home_site');
        }
        


        return $this->render('registration/registerStep2.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    /**
     * @Route("/verify/email", name="app_verify_email")
     */
    public function verifyUserEmail(Request $request): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        // validate email confirmation link, sets User::isVerified=true and persists
        try {
            $this->emailVerifier->handleEmailConfirmation($request, $this->getUser());
        } catch (VerifyEmailExceptionInterface $exception) {
            $this->addFlash('verify_email_error', $exception->getReason());

            return $this->redirectToRoute('app_register');
        }

        // @TODO Change the redirect on success and handle or remove the flash message in your templates
        $this->addFlash('success', 'Your email address has been verified.');

        return $this->redirectToRoute('home');
    }


    /**
     * @Route("verify/email/{token}", name="email_verify")
     *
     * 
     */
    public function activationEmail( $token, UserRepository $usersRepo )
    {
        // On vérifie l'existence du token_get_all
        $user = $usersRepo->findOneBy( [
            'activation_token' => $token
        ] );

        // Si aucun utilisateur n'existe avec ce token
        if ( !$user ) {
            throw $this-> createNotFoundException( "Cet utilisateur n'existe pas" );
        }

        // Sinon on supprime le token 
        $user->setActivationToken( null );
        $user->setIsVerified( true );
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($user);
        $entityManager->flush();

         
        $this->addFlash( 'success' , 'Adresse mail vérifié avec succès' );

        return $this->redirectToRoute( 'account' );
    }
}
