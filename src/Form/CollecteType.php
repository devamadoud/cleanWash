<?php

namespace App\Form;

use App\Entity\Collecte;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CollecteType extends AbstractType
{
  public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('collecteDetailles', CollectionType::class, [
                'entry_type' => CollecteDetailsType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'label' => 'Ajouter les vêtements à laver',
                'entry_options' => [ 
                    'label' => false 
                ],
                'attr' => [
                    'class' => 'my-4',
                    'data-controller' => 'form-collection',
                    'data-form-collection-form-type-value' => 'addable',
                    'data-form-collection-add-label-value' => 'Ajouter un vêtement',
                    'data-form-collection-delete-label-value' => '<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="currentColor" viewBox="0 0 448 512"><!--!Font Awesome Free 6.5.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M135.2 17.7C140.6 6.8 151.7 0 163.8 0H284.2c12.1 0 23.2 6.8 28.6 17.7L320 32h96c17.7 0 32 14.3 32 32s-14.3 32-32 32H32C14.3 96 0 81.7 0 64S14.3 32 32 32h96l7.2-14.3zM32 128H416V448c0 35.3-28.7 64-64 64H96c-35.3 0-64-28.7-64-64V128zm96 64c-8.8 0-16 7.2-16 16V432c0 8.8 7.2 16 16 16s16-7.2 16-16V208c0-8.8-7.2-16-16-16zm96 0c-8.8 0-16 7.2-16 16V432c0 8.8 7.2 16 16 16s16-7.2 16-16V208c0-8.8-7.2-16-16-16zm96 0c-8.8 0-16 7.2-16 16V432c0 8.8 7.2 16 16 16s16-7.2 16-16V208c0-8.8-7.2-16-16-16z"/></svg>',
                ],
            ])
        ;
    }

    public static function typeCollecte($type = null){
        return $type;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Collecte::class,
        ]);
    }
}
