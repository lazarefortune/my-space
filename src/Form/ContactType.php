<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContactType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email', EmailType::class, [
              'label' => "A"
            ])
            ->add('cc', EmailType::class, [
              'label' => "Cc",
              'required' => false
            ])
            ->add('objet')
            ->add('message', TextareaType::class, [
                'attr' => [
                    'rows' => 12
                ]
            ])
            ->add('fichiers', FileType::class, [
                'label' => "Joindre des fichiers",
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Selectionner des fichiers...',
                    'multiple' => true,
                    'onchange' => "javascript:updateList()",
                    'id' => "attachFiles"
                ]
            ])
            // 'multiple' : 'true' , 'id' : "attachFiles", 'onchange': "javascript:updateList()"
            // ->add('envoyer', SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
