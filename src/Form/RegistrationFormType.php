<?php

namespace App\Form;

use App\Entity\User;
use DateTimeImmutable;
use phpDocumentor\Reflection\Types\Void_;
use PHPUnit\TextUI\XmlConfiguration\CodeCoverage\Report\Text;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Event\PostSubmitEvent;
use Symfony\Component\Form\Event\PreSubmitEvent;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('fullName', TextType::class, [
                'label' => 'Nom complet'
            ])
            ->add('adress', TextType::class, [
                'label' => 'Adresse'
            ])
            ->add('userType', ChoiceType::class, [
                'label' => 'Dans quel contexte vous-inscrivez vous sur notre plateforme ?',
                'choices' => [
                    'Pour Gérer mon entreprise de pressing' => 'gerant',
                    'Pour travailler dans une entreprise de pressing' => 'employe'
                ]
            ])
            ->add('telefone', TelType::class, [
                'label' => 'Téléphone'
            ])
            ->add('plainPassword', PasswordType::class, [
                // instead of being set onto the object directly,
                // this is read and encoded in the controller
                'mapped' => false,
                'attr' => ['autocomplete' => 'new-password'],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez entrer un mot de passe',
                    ]),
                    new Length([
                        'min' => 8,
                        'minMessage' => 'Votre mot de passe doit contenir au moins {{ limit }} caractères',
                        // max length allowed by Symfony for security reasons
                        'max' => 4096,
                    ]),
                ],
            ])
            ->add('confirmPassword', PasswordType::class, [
                // instead of being set onto the object directly,
                // this is read and encoded in the controller
                'mapped' => false,
                'attr' => ['autocomplete' => 'confirme-password'],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez entrer un mot de passe',
                    ]),
                    new Length([
                        'min' => 8,
                        'minMessage' => 'Votre mot de passe doit contenir au moins {{ limit }} caractères',
                        // max length allowed by Symfony for security reasons
                        'max' => 4096,
                    ]),
                ],
            ])
            ->add('agreeTerms', CheckboxType::class, [
                'mapped' => false,
                'constraints' => [
                    new IsTrue([
                        'message' => 'Vous devez accepter nos conditions d\'utilisation',
                    ]),
                ],
            ])
            ->add('save', SubmitType::class, [
                'label' => 'S\'inscrire',
                'attr' => ['class' => 'block py-3 px-4 font-medium text-center text-white bg-purple-600 hover:bg-purple-500 active:bg-purple-700 active:shadow-none rounded-lg shadow md:inline']
            ])

            ->addEventListener(FormEvents::POST_SUBMIT, $this->dator(...));
        ;
    }

    public function dator(PostSubmitEvent $event):void
    {
        $data = $event->getData();
        if(!($data instanceof User)){
            return;
        }
        $data->setUpdatedAt(new \DateTimeImmutable());
        if(!$data->getId()){
            $data->setCreatedAt(new \DateTimeImmutable());
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
