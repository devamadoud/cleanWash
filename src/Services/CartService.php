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

        if($product === null || $product->getQuantityStocke() < $quantity) {
            return new Response('Stock insuffisant', Response::HTTP_BAD_REQUEST);
        }

        $cart = $this->request->getSession()->get('cart', []) ?: [];
        $cartTot = $this->request->getSession()->get('cartTot', 0) ?: 0;

        if(isset($cart["product[$id]"]["id"]) && $cart["product[$id]"]["id"] === $id) {
            $cart["product[$id]"]["quantity"] += $quantity;

            if($product->getPromo() > 0) {
                $cart["product[$id]"]["unitPrice"] = $product->getPromoPrice();
                $cartTot += $product->getPromoPrice() * $quantity;
            }else{
                $cart["product[$id]"]["unitPrice"] = $product->getPrice();
                $cartTot += $product->getPrice() * $quantity;
            }
            
        } else {
            $cart["product[$id]"]["id"] = $id;
            $cart["product[$id]"]["quantity"] = $quantity;
            $cart["product[$id]"]["name"] = $product->getName();
            $cart["product[$id]"]["img"] = $product->getProductImage();

            // Si le produit est en promo
            if($product->getPromo() > 0) {

                $cart["product[$id]"]["unitPrice"] = $product->getPromoPrice();
                $cartTot += $product->getPromoPrice() * $quantity;

            }else{
            
                $cart["product[$id]"]["unitPrice"] = $product->getPrice();
                $cartTot += $product->getPrice() * $quantity;
            }
        }

        $this->request->getSession()->set('cart', $cart);
        $this->request->getSession()->set('cartTot', $cartTot);
        return new Response (count($cart), Response::HTTP_OK);
    }

    public function getProduct($cart)
    {
        $products = [];

        if(!empty($cart)){
            foreach($cart as $key => $value) {
                $products[$key]['id'] = $value['id'];
                $products[$key]['name'] = $value['name'];
                $products[$key]['img'] = $value['img'];
                $products[$key]['unitPrice'] = $value['unitPrice'];
                $products[$key]['quantity'] = $value['quantity'];
            }
        }

        return $products;
    }

    public function getCart()
    {
        return $this->request->getSession()->get('cart');
    }

    public function delete(int $id){
        $cart = $this->getCart();

        if(isset($cart["product[$id]"]["id"]) && $cart["product[$id]"]["id"] === $id) {
            $cartTot = $this->request->getSession()->get('cartTot') - $cart["product[$id]"]["unitPrice"] * $cart["product[$id]"]["quantity"];
            $this->request->getSession()->set('cartTot', $cartTot);
            unset($cart["product[$id]"]);
            $this->request->getSession()->set('cart', $cart);
        }

        return new Response('', Response::HTTP_OK);
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