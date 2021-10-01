<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class EditUserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nom', TextType::class)
            ->add('prenom', TextType::class)
            ->add('email', EmailType::class, [
                'constraints' => [
                    new NotBlank([
                        'message' => 'Merci de saisir une adresse email'
                        ])
                    ],
                    'required' => true,
                    'attr' => [
                        'class' => 'form-control',
                        'readonly' => true
                    ]
            ])
            ->add('secondEmail', EmailType::class, [
                'required' => false,
                'label' => 'Deuxième adresse email',
                'attr' => [
                    'class' => 'form-control',
                    'readonly' => true
                ]
            ])
            ->add('login', TextType::class, [
                'attr' => [
                    'class' => 'form-control',
                    'readonly' => true
                ]
            ])
            // ->add('password')
            ->add('roles', ChoiceType::class, [
                'choices' => [
                    'utilisateur' => 'ROLE_USER',
                    'membre' => 'ROLE_MEMBER',
                    'administrateur' => 'ROLE_ADMIN',
                    'super admin' => 'ROLE_SUPER_ADMIN'
                ],
                'expanded' => true,
                'multiple' => true,
                'label' => 'Rôles',
                'required' => true,
            ])
            // ->add('reset_token')
            ->add('isVerified', CheckboxType::class, [
                'required' => false,
                'label' => 'adresse mail valide'
            ])
            // ->add('activation_token')
            ->add('Valider' , SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
