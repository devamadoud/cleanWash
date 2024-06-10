<?php

namespace App\Controller;

use App\Entity\Customer;
use App\Form\CheckoutType;
use App\Repository\ProductRepository;
use App\Services\CartService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
#[Route('/cart')]
class CartController extends AbstractController
{
    #[Route('/', name: 'cart.index')]
    public function index(Request $request, CartService $cartService, EntityManagerInterface $em): Response
    {
        $cart = $request->getSession()->get('cart');
    
        $products = $cartService->getProduct($cart);

        $cartTot = $request->getSession()->get('cartTot');

        $form = $this->createForm(CheckoutType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $dataCustomer = $data['customer'];
            
            $customer = $em->getRepository(Customer::class)->findOneBy(['phoneNumber' => $dataCustomer->getPhoneNumber()]);

            if(!$customer){
                $customer = $dataCustomer;
            }

            $request->getSession()->set('paymentMethodes', $data['paymentMethodes']);
            
            $em->persist($customer);
            $em->flush();
            if($data['paymentMethodes'] === 'on-delivery'){
                return $this->redirectToRoute('order.new', ['customer' => $customer->getId()]);
            }

            if($data['paymentMethodes'] === 'online'){
                $this->addFlash("warning", "La methode de paiement que vous avez choisit n'est paas encore disponible, vous pourrait payer votre commande a la livraison.");
                return $this->redirectToRoute('order.new', ['customer' => $customer->getId()]);
            }
        }
        
        return $this->render('cart/index.html.twig', [
            'products' => $products,
            'cartTot' => $cartTot,
            'form' => $form,
        ]);
    }

    #[Route('/add/{id}/{quantity}', name: 'cart.add', methods: ['POST', 'GET'])]
    public function addtocart(Request $request, CartService $cartService, int $id, int $quantity = 1): Response
    { 
        if($cartService->add($id, $quantity)->getStatusCode() === Response::HTTP_OK) {
            $dataResponse = [
                'articleCount' => count($cartService->getCart()),
                'cartTot' => $cartService->getTotal()
            ];
            return new Response(json_encode($dataResponse), Response::HTTP_OK);
        }elseif($cartService->add($id, $quantity)->getStatusCode() === Response::HTTP_BAD_REQUEST) {
            $this->addFlash('error', 'Le produit n\'a pas pu etre ajouté au panier le stock est insuffisant');
            return $this->redirectToRoute('cart.index');
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

    #[Route('/delete/{id}', name: 'cart.delete', methods: ['POST', 'GET'])]
    public function delete(int $id, CartService $cartService): Response
    {

        if($cartService->delete($id)->getStatusCode() === Response::HTTP_OK) {

            $dataResponse = [
                'articleCount' => count($cartService->getCart()),
                'cartTot' => $cartService->getTotal(),
                'message' => 'Le produit a bien ete supprimé du panier',
                'code' => Response::HTTP_OK
            ];

            return new Response(json_encode($dataResponse), Response::HTTP_OK);
        }

        $dataResponse = [
            'message' => 'Le produit n\'a pas pu etre supprimé du panier',
            'code' => Response::HTTP_BAD_REQUEST
        ];

        return new Response(json_encode($dataResponse), Response::HTTP_BAD_REQUEST);
    }
}
