<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('profile_picture', FileType::class, [
                'label' => 'Profile Picture',
                'required' => false,
                'mapped' => false,
            ])
            ->add('nom', TextType::class, [
                'required' => true,
            ])
            ->add('prenom', TextType::class, [
                'required' => true,
            ])
            ->add('email', EmailType::class,[
                'required' => true,
            ])
            ->add('secondEmail', EmailType::class, [
                'required' => false
            ])
            ->add('phone', TextType::class, [
                'required' => false,
            ])
            ->add('login', TextType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
