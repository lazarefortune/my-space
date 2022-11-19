<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ChangePasswordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('currentPassword', PasswordType::class, [
                'label' => 'Mot de passe actuel',
                'attr' => [
//                    'placeholder' => 'Votre mot de passe actuel',
                    'class' => 'form-control'
                ]
            ])
            ->add('newPassword', PasswordType::class, [
                'label' => 'Nouveau mot de passe',
                'attr' => [
//                    'placeholder' => 'Votre nouveau mot de passe',
                    'class' => 'form-control'
                ]
            ])
            ->add('newPasswordConfirm', PasswordType::class, [
                'label' => 'Confirmation du mot de passe',
                'attr' => [
//                    'placeholder' => 'Confirmez votre nouveau mot de passe',
                    'class' => 'form-control'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
