<?php

namespace App\Form;

use App\Entity\Shop;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Event\PostSubmitEvent;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ShopType extends AbstractType
{
    public function __construct(private Security $security)
    {
        
    }
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('comName')
            ->add('adress')
            ->addEventListener(FormEvents::POST_SUBMIT, $this->dator(...))
        ;
    }

    public function dator(PostSubmitEvent $event): void
    {
        $data = $event->getData();

        if(!($data instanceof Shop)){
            return;
        }
        $data->setUpdatedAt(new \DateTimeImmutable());
        if(!$data->getId()){
            $data->setCreatedAt(new \DateTimeImmutable());
        }

        $data->setOwner($this->security->getUser());
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Shop::class,
        ]);
    }
}
