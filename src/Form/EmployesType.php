<?php

namespace App\Form;

use App\Entity\Employe;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EmployesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('phoneNumber', TelType::class, [
                'label' => 'Veuillez entrer le numéro de téléfone de votre recrut.'
            ])
            ->add('employe', HiddenType::class)
            ->add('emPoste', ChoiceType::class, [
                'label' => 'Choisir le poste...',
                'choices' => [
                    'Caissier' => 'caissier',
                    'Laveur' => 'laveur',
                    'Collecteur/Livreur' => 'collecteur',
                ]
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
