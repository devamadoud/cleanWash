<?php

namespace App\Form;

use App\Entity\Collecte;
use App\Entity\CollecteDetaillesPea;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CollecteDetaillesPeaType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('pea', null, [
                'label' => 'Pois totale en Kg',
                'required' => true,
                'label_attr' => [
                    'class' => 'block text-sm font-medium leading-6 text-gray-900',
                ],
                'attr' => [
                    'placeholder' => 'Pois totale en Kg',
                ],
                'row_attr' => [
                    'class' => 'flex-2 form-collection-type',
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CollecteDetaillesPea::class,
        ]);
    }
}
