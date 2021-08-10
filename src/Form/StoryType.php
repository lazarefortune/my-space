<?php

namespace App\Form;

use App\Entity\Inspiration;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class StoryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class ,[
                "label" => "Titre*",
                "required" => true,
                "attr" => [
                    "maxlength" => 60,
                    "placeholder" => "Donnez un titre à votre story"
                ]
            ])
            ->add('description', TextareaType::class, [
                "label" => "Description*",
                "attr" => [
                    'id' => "summernote",
                    "placeholder" => "Ecrivez ce que vous souhaitez ...",
                    "cols" => 30,
                    "rows" => 10
                    ]
                ])
            ->add('created_at', DateTimeType::class, [
                "label" => "Date de publication*",
                "attr" => [
                    'class' => "font-weight-bold",
                    'value' => date("Y-m-d H:i:s", strtotime("now") )
                    ]
            ] )
            ->add('statut', ChoiceType::class, [
                'multiple' => false,
                'attr' => [
                    'class' => "col-md-4"
                ],
                'choices' => [
                    'Public (visible par tous)' => 'public',
                    'Privée' => 'privee'
                ]
        ])
            ->add("Publier", SubmitType::class, [
                "label" => "Publier la story"
            ] )
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Inspiration::class,
        ]);
    }
}
