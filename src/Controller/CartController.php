<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use App\Services\CartService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
#[Route('/cart')]
class CartController extends AbstractController
{
    #[Route('/', name: 'cart.index')]
    public function index(Request $request, ProductRepository $productRepository): Response
    {
        $cart = $request->getSession()->get('cart');
    
        $products = [];
        if(!empty($cart)){
            foreach($cart as $key => $value) {
                $products[$key]['name'] = $value['name'];
                $products[$key]['img'] = $value['img'];
                $products[$key]['unitPrice'] = $value['unitPrice'];
                $products[$key]['quantity'] = $value['quantity'];
            }
        }

        $cartTot = $request->getSession()->get('cartTot');

        return $this->render('cart/index.html.twig', [
            'products' => $products,
            'cartTot' => $cartTot
        ]);
    }

    #[Route('/{id}/{quantity}/add', name: 'cart.add', methods: ['POST', 'GET'])]
    public function addtocart(int $id, int $quantity, Request $request, CartService $cartService): Response
    { 
        if($cartService->add($id, $quantity)->getStatusCode() === Response::HTTP_OK) {
            $this->addFlash('success', 'Le produit a bien ete ajouté au panier');
            return new Response('Success', Response::HTTP_SEE_OTHER);
        }

        $this->addFlash('error', 'Le produit n\'a pas pu etre ajouté au panier');
        return new Response('Produit indisponible', Response::HTTP_BAD_REQUEST);
    }

    #[Route('/clear', name: 'cart.clear', methods: ['POST', 'GET'])]
    public function clear(CartService $cartService)
    {
        $cartService->clear();
        $this->addFlash('success', 'Le panier a bien ete vide');
        return $this->redirectToRoute('cart.index');
    }
}
