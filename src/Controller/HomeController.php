<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function index(ProductRepository $productRepository, Request $request): Response
    {
        $user = $this->getUser();
        $product = $productRepository->findAll();

        if ($user instanceof User){
            if($user->getShop() == null and $user->getJob() == null or $user->getShop() == null and $user->getJob() == null or $user->getJob() != null and $user->getJob()->getRevokedAt() != null){
                return $this->render('home/notEmployedHome.html.twig');
            }else {
                return $this->render('home/fullyAuthHome.html.twig');
            }
        }
        return $this->render('home/index.html.twig', [
            'products' => $product
        ]);
    }
}
