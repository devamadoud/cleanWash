<?php

namespace App\Form;

use App\Entity\Customer;
use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Event\PostSubmitEvent;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CustomerType extends AbstractType
{

    public function __construct(private UserRepository $userRepository, private Security $security)
    {
        
    }
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('fullName', null, [
                'label' => 'Nom et Prénom',
            ])
            ->add('phoneNumber', null, [
                'label' => 'Téléphone',
            ])
            ->add('adress', null, [
                'label' => 'Adresse',
            ])
            ->add('coordLng', TextType::class, [
                'attr' => ['placeholder' => 'Longitude'],
                'label' => false,
                'required' => true
            ])
            ->add('coordLat', TextType::class, [
                'attr' => ['placeholder' => 'Latitude'],
                'label' => false,
                'required' => true
            ])
            ->addEventListener(FormEvents::POST_SUBMIT, $this->shopAttribiator(...))
        ;
    }

    public function shopAttribiator(PostSubmitEvent $event):void
    {
        $data = $event->getData();

        if(!($data instanceof Customer)){
            return;
        }

        // verifier si c'est un utilisateur connecté qui a soumis le formulaire
        if(!$this->security->isGranted('IS_AUTHENTICATED_REMEMBERED')){
            // Verifier si c'est un nouveau client ou un client existant
            if(!$data->getId()){

                $defaultShopTelefone = '771051360';

                $defaultShop = $this->userRepository->findOneBy(['telefone' => $defaultShopTelefone])->getShop();

                $data->setShop($defaultShop);
            }
        }

        $connectedOwner = $this->security->getUser();

        if(!($connectedOwner instanceof User)){
            return;
        }

        // verifier si c'est un utilisateur connecté qui a soumis le formulaire
        if($this->security->isGranted('IS_AUTHENTICATED_REMEMBERED') and $connectedOwner->getShop() != null or $connectedOwner->getJob() != null){

            $shop = $connectedOwner->getShop();
            if($connectedOwner->getJob() != null and $connectedOwner->getJob()->getPoste() == 'collecteur'){
                $shop = $connectedOwner->getJob()->getShop();
            }

            // Verifier si c'est un nouveau client ou un client existant
            if(!$data->getId()){
                $data->setShop($shop);
            }
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Customer::class,
        ]);
    }
}
