<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ResetPasswordType;
use App\Repository\UserRepository;
use App\Services\SendMailService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;

class SecurityController extends AbstractController
{
    /**
     * @Route("/connexion", name="app_login")
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('home');
        }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('auth/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    /**
     * @Route("/deconnexion", name="app_logout")
     */
    public function logout()
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    /**
     * @Route("/mot-de-passe/reinitialisation" , name="forgotten_password")
     */
    public function forgotten_password( UserRepository $users, SendMailService $mailer , TokenGeneratorInterface $tokenGenerator, Request $request ) : Response
    {
        $form = $this->createForm( ResetPasswordType::class );

        $form->handleRequest( $request );
        
        if ( $form->isSubmitted() && $form->isValid()  ) {
            
            // On récupère les données
            $donnees = $form->getData();

            // On cherche un utilisateur ayant cet e-mail
            $user = $users->findByUsername( $donnees['email'] );

            // Si l'utilisateur n'existe pas
            if ( $user === null ) {
                // On envoie une alerte disant que l'adresse e-mail est inconnue
                $this->addFlash( 'danger', 'Cet identifiant est inconnu de nos services' );

                // On retourne sur la page d'oublie de mot de passe
                return $this->redirectToRoute( 'forgotten_password' );
            }
            if ( $user->getEmail() === null ) {

                $this->addFlash('danger', 'Cet utilisateur ne possède pas d\'adresse mail');

                // On retourne sur la page de réinitialisation du mot de passe
                return $this->redirectToRoute('forgotten_password');
            }
            // On génère un token
            $token = $tokenGenerator->generateToken();

            // On essaie d'écrire le token en base de données
            try {
                $user->setResetToken( $token );
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($user);
                $entityManager->flush();
            } catch (\Exception $e) {
                $this->addFlash('warning', $e->getMessage());
                return $this->redirectToRoute('forgotten_password');
            }

            // On génère l'URL de réinitialisation de mot de passe
            $url = $this->generateUrl('reset_password', array('token' => $token), UrlGeneratorInterface::ABSOLUTE_URL);

            // On génère l'e-mail
            $mailer->sendMail(
                    [ $this->getParameter('send_mail_user') ],
                    [ $user->getEmail() ],
                    'Réinitialisation de votre mot de passe',
                    'password/reset_password',
                    [
                        'url' => $url,
                        'user' => $user
                    ]
                );

            // On crée le message flash de confirmation
            $this->addFlash('success', 'E-mail de réinitialisation du mot de passe envoyé !');

            // On redirige vers la page de soumission
            return $this->redirectToRoute('forgotten_password');
        }

        return $this->render('auth/password/forgotten.html.twig', ['emailForm' => $form->createView()]);
    }


    /**
     * @Route("/mot-de-passe/reinitialisation/{token}", name="reset_password")
     */
    public function resetPassword(Request $request, string $token, UserPasswordEncoderInterface $passwordEncoder)
    {
        // On cherche un utilisateur avec le token donné
        $user = $this->getDoctrine()->getRepository(User::class)->findOneBy(['reset_token' => $token]);

        // Si l'utilisateur n'existe pas
        if ( $user === null ) {
            // On affiche une erreur
            $this->addFlash('danger', 'Token Inconnu');
            return $this->redirectToRoute('forgotten_password');
        }

        // Si le formulaire est envoyé en méthode post
        if ($request->isMethod('POST')) {
            // On supprime le token
            $user->setResetToken(null);
            $newPassword = $request->request->get('password');

            if ( $newPassword == null || $newPassword == '' ) {
                $this->addFlash('danger', 'Veuillez renseigner tous les champs');
            } else {
                // On chiffre le mot de passe
                $user->setPassword( $passwordEncoder->encodePassword( $user, $newPassword ) );

                // On enregistre l'utilisateur en BDD
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($user);
                $entityManager->flush();

                // On crée le message flash de confirmation
                $this->addFlash('success', 'Mot de passe mis à jour');

                // On redirige vers la page de connexion
                return $this->redirectToRoute('app_login');
            }

            return $this->redirectToRoute('reset_password', [
                'token' => $token,
                'user' => $user
            ]);

            
        } else {
            // Si on n'a pas reçu les données, on affiche le formulaire
            return $this->render('auth/password/reset_password.html.twig', [
                'token' => $token,
                'user' => $user
            ]);
        }
    }
}
