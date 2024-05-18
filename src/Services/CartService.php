<?php
namespace App\Services;

use App\Repository\ProductRepository;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;

class CartService
{
    private RequestStack $request;
    private ProductRepository $productRepository;
    public function __construct(RequestStack $request, ProductRepository $productRepository)
    {
        $this->request = $request;
        $this->productRepository = $productRepository;
    }
    public function add(int $id, int $quantity): Response|null
    {
        $product = $this->productRepository->findOneBy(['id' => $id]);

        if($product === null || $product->getQuantityStocke() === 0) {
            return new Response('Produit indisponible', Response::HTTP_BAD_REQUEST);
        }

        $cart = $this->request->getSession()->get('cart', []) ?: [];
        $cartTot = $this->request->getSession()->get('cartTot', 0) ?: 0;

        if(isset($cart["product[$id]"]["id"]) && $cart["product[$id]"]["id"] === $id) {
            $cart["product[$id]"]["quantity"] += $quantity;
            $cartTot += $product->getPrice() * $quantity;
        } else {
            $cart["product[$id]"]["id"] = $id;
            $cart["product[$id]"]["quantity"] = $quantity;
            $cart["product[$id]"]["name"] = $product->getName();
            $cart["product[$id]"]["img"] = $product->getProductImage();
            $cart["product[$id]"]["unitPrice"] = $product->getPrice();
            $cartTot += $cart["product[$id]"]["unitPrice"] * $quantity;
        }

        $this->request->getSession()->set('cart', $cart);
        $this->request->getSession()->set('cartTot', $cartTot);

        return new Response ('Success', Response::HTTP_OK);
    }

    public function getCart()
    {
        return $this->request->getSession()->get('cart');
    }

    public function getTotal()
    {
        return $this->request->getSession()->get('cartTot');
    }

    public function removeTot()
    {
        return $this->request->getSession()->remove('cartTot');
    }

    public function clear()
    {
        $session = $this->request->getSession();
        $session->remove('cart');
        $this->removeTot();
    }
}