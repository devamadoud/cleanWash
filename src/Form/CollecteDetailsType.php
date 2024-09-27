<?php

namespace App\Form;

use App\Entity\ClothingType;
use App\Entity\CollecteDetailles;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CollecteDetailsType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('clothingType', EntityType::class, [
                'class' => ClothingType::class,
                'choice_label' => 'name',
                'label' => false,
                'label_attr' => [
                    'class' => 'block text-sm font-medium leading-6 text-gray-900'
                ],
                'attr' => [
                    'class' => 'mt-2 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6'
                ],
                'placeholder' => 'Choisir le type de vêtement...',
                'row_attr' => [
                    'class' => 'flex-2 form-collection-type'
                ]
            ])
            ->add('quantity', NumberType::class, [
                'label' => false,
                'attr' => [
                    'placeholder' => 'Quantité Ex: 2',
                    'class' => 'mt-2 block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6'
                ],
                'row_attr' => [
                    'class' => 'flex-initial w-32 form-collection-type'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CollecteDetailles::class,
        ]);
    }
}
