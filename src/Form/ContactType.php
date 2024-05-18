<?php

namespace App\Form;

use App\Entity\Contact;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContactType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('phone', TelType::class, [
                'label' => 'Téléphone',
                'required' => true,
                'attr' => [
                    'placeholder' => 'Téléphone',
                ]
            ])
            ->add('subject', TextType::class, [
                'required' => true,
                'label' => 'Sujet',
                'attr' => [
                    'placeholder' => 'Le sujet de votre message',
                ]
            ])
            ->add('message', TextareaType::class, [
                'required' => true,
                'label' => 'Message',
                'attr' => [   
                    'placeholder' => 'Votre message...',
                    'rows' => 6
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Contact::class,
        ]);
    }
}
