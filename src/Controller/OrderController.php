<?php

namespace App\Controller;

use App\Data\collecteSearchData;
use App\Entity\Customer;
use App\Entity\Order;
use App\Entity\OrderDetailles;
use App\Entity\Payment;
use App\Entity\User;
use App\Form\CollecteFilterType;
use App\Repository\OrderRepository;
use App\Repository\ProductRepository;
use App\Services\CartService;
use App\Services\InvoiceService;
use App\Services\PayOutService;
use App\Services\ShipeService;
use App\Services\UniqueRefGenerator;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[Route('/orders')]
class OrderController extends AbstractController
{
    #[Route('/', name: 'order.index')]
    public function index(OrderRepository $orderRepository, Request $request): Response
    {
        $user = $this->getUser();
        if(!($user instanceof User)) {
            $this->addFlash('error', "Vous devez vous identifier pour accéder a la liste des commandes !");
            return $this->redirectToRoute('app_login');
        }

        $reset = $request->query->get('reset', null);
        if($reset){
            return $this->redirectToRoute('order.index');
        }

        $orderDatasearch = new collecteSearchData();

        if($user->getShop()){
            $orderDatasearch->shop = $user->getShop();
        }

        if($user->getJob()){
            $orderDatasearch->shop = $user->getJob()->getShop();
        }

        $orderDatasearch->page = $request->query->getInt('page', 1);
        $orderFormFilter = $this->createForm(CollecteFilterType::class, $orderDatasearch);
        $orderFormFilter->handleRequest($request);


        $orders = $orderRepository->findOrderByShop($orderFormFilter->getData());

        return $this->render('order/index.html.twig', [
            'orders' => $orders,
            'filterForm' => $orderFormFilter
        ]);
    }

    #[Route('/new/{customer}', name: 'order.new', methods: ['GET', 'POST'])]
    public function new(CartService $cartService, PayOutService $payOutService, UrlGeneratorInterface $urlGenerator, Request $request, ProductRepository $productRepository, EntityManagerInterface $entityManager, Customer $customer, InvoiceService $invoiceService): Response
    {

        if(!($customer instanceof Customer)) {
            $url = $urlGenerator->generate($request->headers->get('referer'), [], UrlGeneratorInterface::ABSOLUTE_URL);
            return $this->redirect($url);
        }

        $cart = $request->getSession()->get('cart');
        $productsIds = $cartService->getProduct($cart);
        $cartTot = $cartService->getTotal();

        $order = new Order();
        $referenceGenerator = new UniqueRefGenerator($entityManager);
        
        try{
            foreach($productsIds as $key => $value) {
                $orderDetailles = new OrderDetailles();

                $orderDetailleRef = $referenceGenerator->generateOrderDetaillesReference(8);

                $product = $productRepository->find($value['id']);
                
                if($product->getQuantityStocke() < $value['quantity']) {
                    $this->addFlash('warning', 'La quantité de ce produit ' . $product->getName() . ' est insuffisante, il reste ' . $product->getQuantityStocke() . ' unite(s)');
                    return $this->redirectToRoute('cart.index');
                }

                $product->setQuantityStocke($product->getQuantityStocke() - $value['quantity'])
                    ->setQuantitySales($product->getQuantitySales() + $value['quantity'])
                ;

                $orderDetailles->setQuantity($value['quantity'])
                    ->setUnitPrice($value['unitPrice'])
                    ->setReference($orderDetailleRef)
                    ->setProduct($product)
                    ->setStatus("En attente")
                ;

                $order->addOrderDetaille($orderDetailles);
            }
            
            $paymentChoice = $request->getSession()->get('paymentMethodes');
            $request->getSession()->remove('paymentMethodes');

            $shop = $entityManager->getRepository(User::class)->findOneBy(['telefone' => '771051360'])->getShop();
            $caracteres = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
            $code = substr(str_shuffle($caracteres), 0, 4);
            $orderRef = $referenceGenerator->generateOrderReference(5);

            $order->setCreatedAt(new \DateTimeImmutable())
                ->setUpdatedAt(new \DateTimeImmutable())
                ->setStatus('En attente')
                ->setCustomer($customer)
                ->setTotale($cartTot)
                ->setShop($shop)
                ->setReference($orderRef)
                ->setSecret($code)
                ->setPaymentChoice($paymentChoice)
            ;

            $invoice = $invoiceService->createInvoice($order);

            $order->setInvoice($invoice);


            // $resp = $payOutService->payOut($customer->getPhoneNumber(), $cartTot);

            $entityManager->persist($order);
            $entityManager->flush();
            $request->getSession()->remove('cart');
            $request->getSession()->remove('cartTot');

            /*
            if($resp->getReasonPhrase() === 'OK') {
                $this->addFlash('success', 'Votre commande a bien été enregistrée, notre équipe vous contactera dans les meilleurs delais.');
                return $this->redirectToRoute('home');
            }
            **/
        }catch(\Exception $e) {
            $this->addFlash('error', 'Une erreur est survenue'.$e->getMessage());
            return $this->redirectToRoute('home');
        }

        if($paymentChoice == "online"){
            return $this->redirectToRoute('payment.payout', ['ref' => $order->getReference()]);
        }

        $this->addFlash('success', 'Votre commande a bien été enregistrée, notre équipe vous contactera dans les meilleurs delais.');
        return $this->redirectToRoute('home');
    }

    #[Route('/{id}/show', name: 'order.show', methods: ['GET', 'POST'])]
    public function show(Order $order): Response
    {
        $user = $this->getUser();

        if(!($user instanceof User)){
            $this->addFlash('error', "Vous devez vous identifier pour encaisser un paiement.");
            return $this->redirectToRoute('app_login');
        }

        return $this->render('order/show.html.twig', [
            'order' => $order,
        ]);
    }

    #[Route('/{order}/confirme', name: 'order.confirme', methods: ['GET'])]
    public function confirme(Order $order, Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();

        $referer = $request->headers->get('referer');

        if(!($user instanceof User)){
            $this->addFlash('error', "Vous devez vous identifier pour confirmer une commande.");
            return $this->redirectToRoute('app_login');
        }

        if(!$order){
            $this->addFlash('error', "Une erreur c'est produit, veuillez reéssayer plus tard, si le problème persiste contactez-nous.");
            return $this->redirectToRoute('app_login');
        }

        if($user->getJob()){
            if($user->getJob()->getShop() != $order->getShop()){
                $this->addFlash('error', "Vous ne pouvez pas valider la commande d'une autre boutique !");
                return $this->redirect($referer);
            }
        }

        if($user->getShop()){
            if($user->getShop() != $order->getShop()){
                $this->addFlash('error', "Vous ne pouvez pas valider la commande d'une autre boutique !");
                return $this->redirect($referer);
            }
        }

        if($order->getStatus() != 'En attente'){
            $this->addFlash('error', "Une erreur c'est produit, cette commande a déjà été confirmé.");
            return $this->redirectToRoute($referer);
        }

        foreach ($order->getOrderDetailles() as $key => $orderDetailles) {
            $orderDetailles->setStatus('En cours de traitement');
            $order->addOrderDetaille($orderDetailles);
        }

        if($order->getPaymentChoice() == 'on-deliver')
        {
            $order->setStatus('En cours de traitement');
        }

        if($order->getPaymentChoice() == 'online' and $order->getPayment() == null)
        {
            $order->setStatus('En attente de paiement');
        }

        if($order->getPaymentChoice() == 'online' and $order->getPayment())
        {
            $order->setStatus('En cours de traitement');
        }

        $entityManager->flush();

        $this->addFlash('success', "Vous avez confirmé la commande, vous devez la traiter et livrer dans les meilleurs délais.");
        return $this->redirect($this->generateUrl('order.show', ['id' => $order->getId()]));
    }

    #[Route('/{orderdetaille}/check-product', name: 'order.check.product', methods: ['GET'])]
    public function checkProduct(Request $request, EntityManagerInterface $entityManager, OrderDetailles $orderdetaille)
    {
        $user = $this->getUser();
        $referer = $request->headers->get('referer');

        if(!($user instanceof User)){
            $this->addFlash("error", "Vous devez vous identifier pour valider la disponibilité d'un produit dans une commande !");
            return $this->redirectToRoute("app_login");
        }

        if($user->getShop() and $user->getShop() != $orderdetaille->getTheOrder()->getShop()){
            $this->addFlash("error", "Une erruer c'est produit, cette commande n'est pas lié a votre boutique; si vous pensez que c'est une erreur, merci de nous contacter !");
            return $this->redirect($referer);
        }

        if($user->getJob() and $user->getJob()->getShop() != $orderdetaille->getTheOrder()->getShop()){
            $this->addFlash("error", "Une erruer c'est produit, cette commande n'est pas lié a votre boutique; si vous pensez que c'est une erreur, merci de nous contacter !");
            return $this->redirect($referer);
        }

        if(!$orderdetaille){
            $this->addFlash("error", "Une erreur c'est produit, veuillez reessayer plus tard, si le probléme persiste merci de nous contacter.");
            return $this->redirect($referer);
        }

        if($orderdetaille->getStatus() == "Validé"){
            $this->addFlash("error", "Ce produit a déjà été validée !");
            return $this->redirect($referer);
        }
        
        if($orderdetaille->getTheOrder()->getStatus() != "En cours de traitement"){
            $this->addFlash("error", "Vous ne pouvez pas encore traiter les produits de cette commande, elle n'a pas été validé ou la commande est en attente de paiement !");
            return $this->redirect($referer);
        }

        $orderdetaille->setStatus("Validé");

        $finish = true;
        foreach ($orderdetaille->getTheOrder()->getOrderDetailles() as $key => $product) {
            if($product->getStatus() != "Validé"){
                $finish = false;
            }
        }

        if($finish){
            $orderdetaille->getTheOrder()->setStatus('En attente de livraison');
        }

        $entityManager->flush();

        $this->addFlash("success", "Produit validé !");
        return $this->redirectToRoute('order.show', ['id' => $orderdetaille->getTheOrder()->getId()]);
    }

    #[Route('/{order}/shipping', name: 'order.shipping', methods: ['GET'])]
    public function shipping(Request $request, Order $order, EntityManagerInterface $entityManager, ShipeService $shipeService)
    {
        $user = $this->getUser();
        $referer = $request->headers->get('referer');

        if(!($user instanceof User)){
            $this->addFlash("error", "Vous devez vous identifier pour éffectuer une livraison !");
            return $this->redirectToRoute("app_login");
        }

        if($user->getShop() and $user->getShop() != $order->getShop()){
            $this->addFlash("error", "Une erruer c'est produit, cette commande n'est pas lié a votre boutique; si vous pensez que c'est une erreur, merci de nous contacter !");
            return $this->redirect($referer);
        }

        if($user->getJob() and $user->getJob()->getShop() != $order->getShop()){
            $this->addFlash("error", "Une erreur c'est produit, cette commande n'est pas lié a votre boutique; si vous pensez que c'est une erreur, merci de nous contacter !");
            return $this->redirect($referer);
        }

        if(!$order){
            $this->addFlash("error", "Une erreur c'est produit, veuillez reessayer plus tard, si le probléme persiste merci de nous contacter.");
            return $this->redirect($referer);
        }

        if($order->getStatus() != "En attente de livraison"){
            $this->addFlash("error", "Une erreur c'est produit, cette commande n'est pas en attente de livraison.");
            return $this->redirect($referer);
        }

        if($order->getShipe()){
            $this->addFlash("error", "Une erreur c'est produit, cette commande à déjà été livré.");
            return $this->redirect($referer);
        }

        $shipe = $shipeService->ship($order, "En cours", $user);
        $order->setStatus('En cours de livraison')
            ->setShipe($shipe);
        ;

        $entityManager->persist($shipe);
        $entityManager->flush();

        if($order->getPayment()){
            $this->addFlash("success", "Vous ête en route pour livrer la commande de ".$order->getCustomer()->getFullName()." le paiement a déjà été éffectué.");
        }else{
            $this->addFlash("success", "Vous ête en route pour livrer la commande de ".$order->getCustomer()->getFullName()." La facture de cette commande n'as pas été reglé, le client vas payer a la livraison.");
        }
        return $this->redirectToRoute('customer.show', ['id' => $order->getCustomer()->getId()]);
    }

    #[Route('/{order}/shipped', name: 'order.shipped', methods: ['POST', 'GET'])]
    public function shipped(Request $request, Order $order, EntityManagerInterface $entityManager, ShipeService $shipeService)
    {
        $user = $this->getUser();
        $secret = $request->query->get('secret');
        $referer = $request->headers->get('referer');

        if($user instanceof User){
            if($user->getShop() and $user->getShop() != $order->getShop()){
                $this->addFlash("error", "Une erruer c'est produit, cette commande n'est pas lié a votre boutique; si vous pensez que c'est une erreur, merci de nous contacter !");
                return $this->redirect($referer);
            }

            if($user->getJob() and $user->getJob()->getShop() != $order->getShop()){
                $this->addFlash("error", "Une erreur c'est produit, cette commande n'est pas lié a votre boutique; si vous pensez que c'est une erreur, merci de nous contacter !");
                return $this->redirect($referer);
            }
        }

        if(!$order){
            $this->addFlash("error", "Une erreur c'est produit, veuillez reessayer plus tard, si le probléme persiste merci de nous contacter.");
            return $this->redirect($referer);
        }

        if($order->getStatus() != "En cours de livraison"){
            $this->addFlash("error", "Une erreur c'est produit, cette commande n'est pas en cours de livraison.");
            return $this->redirect($referer);
        }

        if(!$secret){
            $this->addFlash('error', "Vous devez saisire le code secret de la commande pour confirmer la livraison.");
            return $this->redirect($request->headers->get('referer'));
        }

        if($secret and $secret != $order->getSecret()){
            $this->addFlash('error', "Le code que vous avez saisit est incorrecte.");
            return $this->redirect($request->headers->get('referer'));
        }

        if($order->getPaymentChoice() == "online" and $order->getPayment()){
            $order->setStatus('Terminé');
        }

        if($order->getPaymentChoice() == "on-deliver" and !$order->getPayment()){
            $order->setStatus('En attente de paiement');
        }

        $shipeService->ship($order, "Terminé", $this->getUser());
        
        $entityManager->flush();

        $referer = $request->headers->get('referer');
        return $this->redirect($referer);
    }

    #[Route('/{order}/checkout-service', name: 'order.checkout', methods: ['POST', 'GET'])]
    public function checkout(Request $request, EntityManagerInterface $entityManager, Order $order)
    {
        $user = $this->getUser();

        if(!($user instanceof User)){
            $this->addFlash('error', "Vous devez vous identifier pour encaisser un paiement.");
            return $this->redirectToRoute('app_login');
        }

        if(!$order){
            $this->addFlash('error', "Une erreur c'est produit, veuillez reéssayer plus tard.");
            return $this->redirect($request->headers->get('referer'));
        }

        if($order->getStatus() != "En attente de paiement" && $order->getPayment() != null){
            $this->addFlash('error', "Une erreur c'est produit, cette commande a déja été payée.");
        }

        if($order->getPayment()){
            $completed = true;
            foreach ($order->getOrderDetailles() as $key => $od) {
                if($od->getStatus() != "Validé"){
                    $completed = false;
                }
            }

            if($completed){
                $order->setStatus('Terminé');
                $entityManager->flush();
                return $this->redirectToRoute('order.show', ['id' => $order->getId()]);
            }
        }
        
        if($order->getPayment() != null){
            $this->addFlash('warning', "Cette collecte as déjà été payé, veuillez verifier la reference et reéssayez !");
            return $this->redirect($request->headers->get('referer'));
        }
        

        if($user->getShop() or $user->getJob() and $user->getJob()->getPoste() == "caissier" or $user->getJob() and $user->getJob()->getPoste() == "collecteur"){

            $paymentMethode = $request->query->get('paymentMethode');

            if(!$paymentMethode and $request->getSession()->get('paymentMethode')){
                $paymentMethode = $request->getSession()->get('paymentMethode');
                $request->getSession()->remove('paymentMethode');
            }

            $amount = $order->getTotale();

            $payment = new Payment();

            $payment->setPaimentMode($paymentMethode)
                ->setPaidAt(new DateTimeImmutable())
                ->setAmount($amount)
                ->setType("Commande de produit")
            ;

            if($user->getJob() and $user->getJob()->getPoste() == "caissier"){
                $payment->setConfirmation(true)
                    ->setCashedBy("Le caissier")
                    ->setStatus('Effectué')
                ;
            }

            if($user->getShop()){
                $payment->setConfirmation(true)
                    ->setCashedBy("Le gérant")
                    ->setStatus('Effectué')
                ;
            }

            if($user->getJob() and $user->getJob()->getPoste() == "collecteur"){
                $payment->setConfirmation(false)
                    ->setCashedBy("Le collecteur")
                    ->setStatus('En attente de confirmation')
                ;
            }

            if($order->getPaymentChoice() == "online"){
                $order->setStatus('En cours de traitement');
            }

            if($order->getPaymentChoice() == "on-deliver"){
                $order->setStatus('Terminé');
            }

            $order->setPaidAt(new DateTimeImmutable())
                ->setPayment($payment)
            ;
            $entityManager->flush();

            $this->addFlash('success', "Paiement effectué avec succés.");
            return $this->redirect($request->headers->get('referer'));
        }
    }

    #[Route('/{order}/{transaction}/online-checkout-service', name: 'order.onlineCheckout', methods: ['POST', 'GET'])]
    public function onlineCheckout(Request $request, EntityManagerInterface $entityManager, Order $order, string $transaction)
    {
        if(!$order){
            $this->addFlash('error', "Une erreur c'est produit, veuillez reéssayer plus tard.");
            return $this->redirect($request->headers->get('referer'));
        }

        if($order->getStatus() != "En attente de paiement" && $order->getPayment() != null){
            $this->addFlash('error', "Une erreur c'est produit, cette commande a déja été payée.");
        }

        if($order->getPayment() != null){
            $this->addFlash('warning', "Cette commande as déjà été payé, veuillez verifier la reference et reéssayez !");
            return $this->redirect($request->headers->get('referer'));
        }
        
        $paymentMethode = "online";

        $amount = $order->getTotale();

        $payment = new Payment();

        $payment->setPaimentMode($paymentMethode)
            ->setPaidAt(new DateTimeImmutable())
            ->setAmount($amount)
            ->setCashedBy("Le client")
            ->setStatus('Effectuée')
            ->setConfirmation(true)
            ->setTransactionId($transaction)
            ->setType("Commande de produit")
        ;

        if($order->getPaymentChoice() == "online"){
            $order->setStatus('En cours de traitement');
            
            foreach ($order->getOrderDetailles() as $key => $od) {
                $od->setStatus('En cours de traitement');
            }
        }

        if($order->getPaymentChoice() == "on-deliver"){
            $order->setStatus('Terminé');
        }

        $order->setPaidAt(new DateTimeImmutable())
            ->setPayment($payment)
        ;
        $entityManager->flush();

        $this->addFlash('success', "Paiement effectué avec succés.");
        return $this->redirectToRoute('home');
    }

    #[Route('/{order}/cancel', name: 'order.cancel', methods: ['GET', 'POST'])]
    public function cancel(Order $order, EntityManagerInterface $entityManager, Request $request): Response
    {
        $user = $this->getUser();
        $secret = $request->query->get('secret');
        $motifCancel = $request->query->get('motifCancel');
        $referer = $request->headers->get('referer');

        if(!($user instanceof User)){
            if(!$secret){
                $this->addFlash('error', "Vous devez saisir le code secret de la commande pour l'annuler.");
                return $this->redirect($referer);
            }

            if($order->getSecret() != $secret){
                $this->addFlash('error', "Le code secret que vous avez saisi est incorrect, veuillez le rentrer correctement.");
                return $this->redirect($referer);
            }
        }

        if($user instanceof User){
            if($user->getJob() and $user->getJob()->getShop() != $order->getShop()){
                $this->addFlash('error', "Vous ne pouvez pas annuler la commande d'une autre boutique !");
                return $this->redirect($referer);
            }

            if($user->getShop() and $user->getShop() != $order->getShop()){
                $this->addFlash('error', "Vous ne pouvez pas annuler la commande d'une autre boutique !");
                return $this->redirect($referer);
            }
        }

        if(!$order){
            $this->addFlash('error', "Une erreur c'est produit, veuillez reéssayer plus tard, si le probleme persiste veuillez nous contacter !");
            return $this->redirect($referer);
        }

        if($order->getStatus() == "Annule"){
            $this->addFlash('warning', "Cette commande a deja été annulée !");
            return $this->redirect($referer);
        }

        if($order->getStatus() != "En attente"){
            $this->addFlash('warning', "Cette commande est en cours de traitement pour l'annuler, veuillez contacter le support au ".$order->getShop()->getPhoneNumber()."");
        }

        foreach ($order->getOrderDetailles() as $key => $od) {
            $product = $od->getProduct();
            $od->setStatus('Annulé');

            $product->setQuantitySales($product->getQuantitySales() - $od->getQuantity())
                ->setQuantityStocke($product->getQuantityStocke() + $od->getQuantity())
            ;
        }

        $order->setStatus('Annulé')
            ->setCanceledAt(new DateTimeImmutable())
            ->setMotifCancel($motifCancel)
        ;

        if($order->getPayment()){
            $order->getPayment()->setStatus("Resturn");
        }

        $entityManager->flush();

        $this->addFlash('success', "La commande a été annulée !");

        if($user){
            return $this->redirectToRoute('order.index');
        }

        return $this->redirectToRoute('home');
    }
}
