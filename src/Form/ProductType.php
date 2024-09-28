<?php

namespace App\Form;

use App\Entity\Categories;
use App\Entity\Product;
use App\Services\CalculatorService;
use App\Services\UserProvider;
use DateTimeImmutable;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Event\PostSubmitEvent;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\UX\Dropzone\Form\DropzoneType;

class ProductType extends AbstractType
{
    private CalculatorService $calculatorService;
    private UserProvider $userProvider;
    private Security $security;
    public function __construct(Security $security, CalculatorService $calculatorService, UserProvider $userProvider)
    {
        $this->calculatorService = $calculatorService;
        $this->userProvider = $userProvider;
        $this->security = $security;
    }
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('productImage', DropzoneType::class, [
                'label' => "Image du produit",
                'attr' => [
                    'placeholder' => "Glisser-déposer ou cliquer pour choisir une image",
                    'accept' => 'image/png, image/jpeg, image/jpg, image/gif',
                    'multiple' => false,
                    'aria-describedby' => "Ajouter l'image du produit",
                ],
                'data' => null,
                'required' => false,
                'data_class' => null,
                'mapped' => false,
            ])

            ->add('name', TextType::class, [
                'label' => "Nom du produit",
                'required' => true,
            ])

            ->add('purchasePrice', NumberType::class, [
                'label' => "Prix d'achat",
                'attr' => [
                    'min' => 100,
                    'step' => 100,
                    'aria-describedby' => "Ajouter le prix d'achat",
                ],
                'required' => true,
            ])

            ->add('price', NumberType::class, [
                'label' => "Prix de vente",
                'attr' => [
                    'min' => 100,
                    'step' => 100,
                    'aria-describedby' => "Ajouter le prix de vente, le prix de vente doit être supérieur au prix d'achat",
                ],
                'required' => true,
            ])

            ->add('quantityStocke', NumberType::class, [
                'label' => "Quantité en stock",
                'attr' => [
                    'min' => 1,
                    'step' => 1,
                    'aria-describedby' => "Ajouter la quantité que vous avez en stock pour ce produit",
                ],
                'required' => true,
            ])

            ->add('promo', NumberType::class, [
                'label' => "Promotion",
                'attr' => [
                    'min' => 0,
                    'step' => 1,
                    'aria-describedby' => "Ajouter la pourcentage de réduction de ce produit (0 pour aucune promotion)",
                ]
            ])
            
            ->add('productDescription', TextareaType::class, [
                'label' => "Description du produit",
                'attr' => [
                    'aria-describedby' => "Ajouter la description detaillée du produit, pour aider les clients à comprendre ce qu'ils achètent",
                ],
                'required' => true,
            ])

            ->add('category', EntityType::class, [
                'class' => Categories::class,
                'choice_label' => 'name',
                'multiple' => true,
                'autocomplete' => true
            ])
            ->addEventListener(FormEvents::POST_SUBMIT, $this->productAttributes(...))
        ;
    }

    public function productAttributes(PostSubmitEvent $event):void
    {
        $data = $event->getData();

        if(!($data instanceof Product)){
            return;
        }

        $user = $this->security->getUser();

        $shop = $this->userProvider->getShop($user);

        // Si l'utilisateur n'est pas le propriaitaire de la boutique, on renvoie une erreur
        if($shop->getOwner() != $user){
            throw new \Exception("Vous n'avez pas le droit d'ajouter ou de modifier des produits.");
        }

        // On calcule le prix de la promotion
        $data->setPromoPrice($this->calculatorService->getPriceOfPromo($data->getPrice(), $data->getPromo()))
            ->setUpdatedAt(new DateTimeImmutable())
        ;

        if(!$data->getId()){
            $data->setCreatedAt(new DateTimeImmutable())
                ->setShop($shop)
            ;
        }

    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
        ]);
    }
}
