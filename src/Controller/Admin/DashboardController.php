<?php

namespace App\Controller\Admin;

use App\Entity\Categories;
use App\Entity\ClothingPea;
use App\Entity\ClothingType;
use App\Entity\Collecte;
use App\Entity\Customer;
use App\Entity\Employe;
use App\Entity\Order;
use App\Entity\Product;
use App\Entity\Shop;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Config\UserMenu;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

class DashboardController extends AbstractDashboardController
{
    #[Route('/admin', name: 'admin')]
    public function index(): Response
    {
        // return parent::index();

        // Option 1. You can make your dashboard redirect to some common page of your backend
        //
        $adminUrlGenerator = $this->container->get(AdminUrlGenerator::class);
        return $this->redirect($adminUrlGenerator->setController(ShopCrudController::class)->generateUrl());

        // Option 2. You can make your dashboard redirect to different pages depending on the user
        //
        // if ('jane' === $this->getUser()->getUsername()) {
        //     return $this->redirect('...');
        // }

        // Option 3. You can render some custom template to display a proper dashboard with widgets, etc.
        // (tip: it's easier if your template extends from @EasyAdmin/page/content.html.twig)
        //
        // return $this->render('some/path/my-dashboard.html.twig');
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('CleanWash');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');
        yield MenuItem::linkToCrud('Boutique', 'fas fa-shop', Shop::class);
        yield MenuItem::linkToCrud('Products', 'fas fa-shopping-cart', Product::class);
        yield MenuItem::linkToCrud('Categories', 'fas fa-list', Categories::class);
        yield MenuItem::linkToCrud('Type de vêtement', 'fas fa-tshirt', ClothingType::class);
        yield MenuItem::linkToCrud('Collecte par poids', 'fas fa-weight', ClothingPea::class);
        yield MenuItem::linkToCrud('Collectes', 'fas fa-bookmark', Collecte::class);
        yield MenuItem::linkToCrud('Comandes', 'fas fa-shopping-cart', Order::class);
        yield MenuItem::linkToCrud('Clients', 'fas fa-users', Customer::class);
        yield MenuItem::linkToCrud('Employes', 'far fa-address-card', Employe::class);
        // yield MenuItem::linkToCrud('The Label', 'fas fa-list', EntityClass::class);
    }

    public function configureUserMenu(UserInterface $user): UserMenu
    {
        return parent::configureUserMenu($user)
            ->addMenuItems([
                Menuitem::linkToRoute('Tableau de bord', 'fa fa-home', 'home'),
            ]);
    }
}
