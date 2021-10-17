<?php

namespace App\Form;

use App\Entity\School;
use App\Entity\Filiere;
use App\Entity\Promotion;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class PromotionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name' , TextType::class, [
                'label' => "Nom de la promotion"
            ])
            ->add('description', TextareaType::class, [
                'label' => "Description de la promotion"
            ])
            ->add('school' , EntityType::class, [
                'label' => 'Etablissement',
                'class' => School::class,
            
                'choice_label' => 'name',
            
                // used to render a select box, check boxes or radios
                // 'multiple' => true,
                // 'expanded' => true,
            ])
            // ->add('filiere' , EntityType::class, [
            //     'label' => 'Filiere',
            //     'class' => Filiere::class,
            
            //     'choice_label' => 'name',
            
            //     // used to render a select box, check boxes or radios
            //     // 'multiple' => true,
            //     // 'expanded' => true,
            // ]);
            // ->add('created_at')
            // ->add('updated_at')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Promotion::class,
        ]);
    }
}
