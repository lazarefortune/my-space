<?php

namespace App\Form;

use App\Entity\Inspiration;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class StoryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class ,[
                "label" => "Titre",
                "required" => false
            ])
            ->add('description', TextareaType::class, [
                "label" => "Description*",
                "attr" => ['id' => "summernote"]
                ])
            ->add('created_at', DateTimeType::class, [
                "label" => "Date de publication*",
                "attr" => ['class' => "font-weight-bold"]
            ] )
            ->add('Enregistrer', SubmitType::class )
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Inspiration::class,
        ]);
    }
}
