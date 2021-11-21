<?php
namespace App\Services;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;

class SendMailService
{

    private $mailer;

    public function __construct( MailerInterface $mailer )
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
        
        $email = (new TemplatedEmail())
                ->subject( $subject )
                // On attribue les expéditeurs
                ->from( $from )
                // On attribue les destinataires
                ->to( $to )
                // On crée le texte avec la vue
                ->htmlTemplate( 'layouts/emails/'. $template .'html.twig' )
                // On ajoute les valeurs de la vue
                ->context( $context );

        $this->mailer->send( $email );
    }

}

