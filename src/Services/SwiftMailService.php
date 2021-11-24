<?php
namespace App\Services;

class SwiftMailService
{

    private $mailer;

    public function __construct( \Swift_Mailer $mailer )
    {
        $this->mailer = $mailer;
    }

    public function sendMail( 
        array $from , 
        array $to , 
        String $subject, 
        String $template, 
        array $context 
    ) : void
    {
        
        $message = (new \Swift_Message())
                ->setSubject( $subject )
                // On attribue l'expéditeur
                ->setFrom( $from )
                // On attribue le destinataire
                ->setTo( $to );
                // On crée le texte avec la vue
                // ->setBody(
                //     $this->renderView(
                //         'layouts/emails/'. $template .'html.twig',
                //         $context
                //     ),
                //     'text/html'
                // );
        $this->mailer->send($message);
    }

}

