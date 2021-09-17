<?php

namespace App\Form;

use App\Entity\User;
use App\Entity\MailSend;
use Doctrine\DBAL\Types\ArrayType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class MailSendType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('toEmail', TextType::class)
            ->add('copyEmail', TextType::class, [
                'required' => false
            ])
            ->add('title')
            ->add('content')
            // ->add('fromEmail')
            // ->add('author' , EntityType::class, [
            //     'class' => User::class,
            //     'choice_label' => 'email'
            // ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => MailSend::class,
        ]);
    }
}
