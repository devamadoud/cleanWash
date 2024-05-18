<?php

namespace App\Controller\Admin;

use App\Entity\Shop;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class ShopCrudController extends AbstractCrudController
{

    public function configureActions(Actions $actions): Actions
    {
        $actions->add(Crud::PAGE_NEW, Action::INDEX, Action::DETAIL)
            ->add(Crud::PAGE_INDEX, Action::DETAIL, Action::NEW)
            ->remove(Crud::PAGE_DETAIL, Action::DELETE)
            ->remove(Crud::PAGE_INDEX, Action::DELETE)
        ;

        return $actions;
    }

    public static function getEntityFqcn(): string
    {
        return Shop::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('comName', 'Nom comercial'),
            TextField::new('adress', 'Adresse de l\'entreprise'),
            AssociationField::new('owner', 'Gérant(e)'),
            ArrayField::new('employes')->onlyOnDetail()
        ];
    }

    public function createEntity(string $entityFqcn)
    {
        $shop = new Shop();
        $shop->setCreatedAt(new \DateTimeImmutable())
            ->setUpdatedAt(new \DateTimeImmutable());

        return $shop;
    }
}
