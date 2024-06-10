<?php

namespace App\Controller;

use App\Entity\Product;
use App\Repository\ProductRepository;
use App\Services\CartService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/product')]
class ProductController extends AbstractController
{
    #[Route('/', name: 'product.index')]
    public function index(): Response
    {
        return $this->render('product/index.html.twig', [
            'controller_name' => 'ProductController',
        ]);
    }

    #[Route('/{id}', name: 'product.show')]
    public function show(Product $product, ProductRepository $productRepository, Request $request, CartService $cartService): Response
    {   
        // Recuperer le numéro de page
        $page = $request->query->get('page', 1);
        // Trouver les produits similaires grace a la categorie
        $products = $productRepository->findByCategory($product->getCategory()[0], $page);

        $form = $this->createFormBuilder()
            ->add('quantity', NumberType::class, [
                'label' => false,
                'attr' => [
                    'min' => 1,
                    'step' => 1,
                    'max' => $product->getQuantityStocke(),
                    'aria-describedby' => "helper-text-explanation"
                ]
            ])->getForm()
        ;

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $quantity = $form->get('quantity')->getData();
            dd($quantity);
            $cartService->add($product->getId(), $quantity);

            $this->addFlash('success', 'Le produit a bien ete ajouté au panier');
            return $this->redirectToRoute('cart.index');
        }

        return $this->render('product/show.html.twig', [
            'product' => $product,
            'products' => $products,
            'form' => $form
        ]);
    }

    #[Route('/{id}/addToCart', name: 'product.addToCart', methods: ['POST'])]
    public function addToCart(Product $product, CartService $cartService, Request $request): Response
    {
        // Recuperer la quantite saisie par l'utilisateur
        $quantity = $request->request->get('quantity');
        
        // Ajouter le produit au panier
        if($quantity and $quantity > 0) {

            $resopnse = $cartService->add($product->getId(), $quantity);

            if($resopnse->getStatusCode() === Response::HTTP_BAD_REQUEST) {
                $referer = $request->headers->get('referer');
                $this->addFlash('error', 'Le produit n\'a pas pu etre ajouté au panier le stock est insuffisant');
                return $this->redirect($referer);
            }

            return $this->redirectToRoute('cart.index');
        }

        // Si la quantité est inferieure ou egale a 0
        if(!$quantity or $quantity <= 0) {
            $this->addFlash('warning', 'La quantité doit etre superieure a 0');
            return $this->redirectToRoute('product.show', ['id' => $product->getId()]);
        }

        return $this->redirectToRoute('product.show', ['id' => $product->getId()]);
    }
}
