<?php

namespace App\Controller\Admin;

use App\Entity\Product;
use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class ProductCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Product::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            ImageField::new('ProductImage')
                ->setUploadDir('/public/images/uploads/products')
                ->setBasePath('/images/uploads/products')
                ->setUploadedFileNamePattern('[randomhash][month][day][timestamp].[extension]'),
            TextField::new('name'),
            NumberField::new('quantityStocke'),
            TextEditorField::new('ProductDescription'),
            NumberField::new('promo'),
            MoneyField::new('price')->setCurrency('XOF')->setStoredAsCents(false),
            MoneyField::new('PurchasePrice')->setCurrency('XOF')->setStoredAsCents(false),
            BooleanField::new('published')->renderAsSwitch(true),
        ];
    }

    public function createEntity(string $entityFqcn)
    {
        $user = $this->getUser();
        if(($user instanceof User)){
            $shop = $user->getShop();
        }

        $product = new Product();

        $product->setCreatedAt(new \DateTimeImmutable())
            ->setUpdatedAt(new \DateTimeImmutable())
            ->setShop($shop)
        ;

        return $product;
    }
}
