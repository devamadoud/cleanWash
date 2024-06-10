<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CheckoutType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('customer', CustomerType::class, [
                "label" => "Vos informations",
                "required" => true,
            ])
            ->add('paymentMethodes', ChoiceType::class, [
                "label" => "Mode de paiement",
                "required" => true,
                "multiple" => false,
                "expanded" => true,
                "choices" => [
                    'A la livraison' => "on-delivery",
                    "Mobile Money" => "online",
                ],
                'data' => "A la livraison",
            ])
            ->add('adress', TextType::class, [
                "label" => "Adresse de facturation (facultatif)",
                "required" => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
