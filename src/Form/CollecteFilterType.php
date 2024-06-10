<?php
namespace App\Form;

use App\Data\collecteSearchData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CollecteFilterType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('ref', null, [
                'label' => false,
                'required' => false,
                'attr' => [
                    'placeholder' => '# Réference:'
                ]
            ])
            ->add('tel', TelType::class, [
                'label' => false,
                'required' => false,
                'attr' => [
                    'placeholder' => 'Telephone:'
                ]
            ])
            ->add('dateFrom', TextType::class, [
                "label" => false,
                "required" => false,
                "attr" => [
                    "placeholder" => "Date de debut",
                    "datepicker-format" => "yyy-MM-dd"
                ]
            ])
            ->add('dateTo', TextType::class, [
                "label" => false,
                "required" => false,
                "attr" => [
                    "placeholder" => "Date de fin",
                    "datepicker-format" => "yyy-MM-dd"
                ]
            ])
            ->add('status', ChoiceType::class, [
                'label' => false,
                'required' => false,
                'placeholder' => 'Par statut',
                'choices' => [
                    "En attente" => "En attente",
                    "En attente de paiement" => "En attente de paiement",
                    "En attente de livraison" => "En attente de livraison",
                    "En cours de livraison" => "En cours de livraison",
                    "En attente de lavage" => "En attente de lavage",
                    "En cours de lavage" => "En cours de lavage",
                    "Terminé" => "Terminé",
                ],
                'attr' => [
                    'placeholder' => '--Par statut--'
                ]
            ])
        ;
    }
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => collecteSearchData::class,
            'method' => 'GET',
            'csrf_protection' => false
        ]);
    }

    public function getBlockPrefix()
    {
        return '';
    }
}