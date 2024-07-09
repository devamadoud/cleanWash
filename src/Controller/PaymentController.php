<?php

namespace App\Controller;

use App\Data\collecteSearchData;
use App\Entity\Collecte;
use App\Entity\Order;
use App\Entity\User;
use App\Form\CollecteFilterType;
use App\Repository\CollecteRepository;
use App\Repository\PaymentRepository;
use App\Services\PayOutService;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/payment')]
class PaymentController extends AbstractController
{
    #[Route('/', name: 'payment.index', methods: ['POST', 'GET'])]
    public function index(Request $request, EntityManagerInterface $entityManager, PaymentRepository $paymentRepository) : Response
    {
        $user = $this->getUser();

        if(!($user instanceof User)){
            $this->addFlash('error', "Vous devez vous identifier pour continuer !");
            return $this->redirectToRoute('app_login');
        }

        $shop = null;
        if($user->getShop()){
            $shop = $user->getShop();
        }

        if($user->getJob() and $user->getJob()->getPoste() == "caissier" or $user->getJob() and $user->getJob()->getPoste() == "collecteur"){
            $shop = $user->getJob()->getShop();
        }

        $collectePayments = $paymentRepository->findByCollecteShop($shop);
        $orderPayments = $paymentRepository->findByOrderShop($shop);

        return $this->render('payment/index.html.twig', [
            'collectePayments' => $collectePayments,
            'orderPayments' => $orderPayments,
        ]);
    }

    #[Route('/collecte', name: 'payment.collecte')]
    public function collecte(PaymentRepository $paymentRepository, Request $request): Response
    {
        $user = $this->getUser();

        if(!($user instanceof User)){
            $this->addFlash('error', "Vous devez vous identifier pour continuer !");
            return $this->redirectToRoute('app_login');
        }

        $reset = $request->query->get('reset', null);
        
        if($reset){
            return $this->redirectToRoute('payment.index');
        }

        $paymentFilterData = new collecteSearchData();

        if($user->getShop()){
            $paymentFilterData->shop = $user->getShop();
        }

        if($user->getJob()){
            $paymentFilterData->shop = $user->getJob()->getShop();
        }

        $paymentFilterData->page = $request->query->get('page', 1);

        $paymentFilterForm = $this->createForm(CollecteFilterType::class, $paymentFilterData);
        $paymentFilterForm->handleRequest($request);

        $payments = $paymentRepository->findCollecteByShop($paymentFilterForm->getData());
        return $this->render('payment/collecte.html.twig', [
            'payments' => $payments,
            'filterForm' => $paymentFilterForm
        ]);
    }

    #[Route('/order', name: 'payment.order')]
    public function order(PaymentRepository $paymentRepository, Request $request): Response
    {
        $user = $this->getUser();

        if(!($user instanceof User)){
            $this->addFlash('error', "Vous devez vous identifier pour continuer !");
            return $this->redirectToRoute('app_login');
        }

        $reset = $request->query->get('reset', null);
        if($reset){
            return $this->redirectToRoute('payment.index');
        }

        $paymentFilterData = new collecteSearchData();

        if($user->getShop()){
            $paymentFilterData->shop = $user->getShop();
        }

        if($user->getJob() and $user->getJob()->getPoste() == "caissier"){
            $paymentFilterData->shop = $user->getJob()->getShop();
        }

        $paymentFilterData->page = $request->query->get('page', 1);

        $paymentFilterForm = $this->createForm(CollecteFilterType::class, $paymentFilterData);
        $paymentFilterForm->handleRequest($request);

        $payments = $paymentRepository->findOrderByShop($paymentFilterForm->getData());
        return $this->render('payment/order.html.twig', [
            'payments' => $payments,
            'filterForm' => $paymentFilterForm
        ]);
    }

    #[Route('/checkout', name: 'payment.checkout', methods: ['POST'])]
    public function checkout(Request $request, EntityManagerInterface $entityManager): Response
    {   
        // Reference de la transaction a traiter, collecte ou order
        $ref = $request->request->get('ref');

        // Type de transaction a traiter
        $type = $request->request->get('type');

        // Methode de paiement, comment le client a reglé le paiement, en espéce ou par mobile money
        $paymentMethode = $request->request->get('paymentMethode');

        // on verifie si $ref existe,
        if($ref){

            // on verifie si c'est une collecte et si oui on recupére la collecte par sa reference
            if($type && $type == "collecte"){

                $collecte = $entityManager->getRepository(Collecte::class)->findOneBy(['reference' => $ref]);
                
                // Si la collecte n'existe pas on redirige l'utilisateur vers la page precedente
                if(!$collecte){
                    $this->addFlash("error", "Aucune collecte ne correspond a la reference que vous venez de saisir, veuillez verifier la reference et reessayez.");
                    $this->redirect($request->headers->get('referer'));
                }

                // Si la collecte existe on redirige vers la page de paiement des collectes
                if($collecte){
                    return $this->redirectToRoute('collecte.checkout', ['collecte' => $collecte->getId()]);
                }
            }

            if($ref && $type == "order"){

                $order = $entityManager->getRepository(Order::class)->findOneBy(['reference' => $ref]);

                // Si la collecte n'existe pas on redirige l'utilisateur vers la page precedente
                if(!$order){
                    $this->addFlash("error", "Aucune commande ne correspond a la reference que vous avez saisie, veuillez verifier la reference et reessayez.");
                    $this->redirect($request->headers->get('referer'));
                }

                if($order){
                    $request->getSession()->set('paymentMethode', $paymentMethode);
                    return $this->redirectToRoute('order.checkout', ['order' => $order->getId()]);
                }
            }
        }
        
        $this->addFlash("error", "Aucune commande ou collecte ne correspond a cette reference, veuillez verifier la reference et ressaiyez.");
        return $this->redirect($request->headers->get('referer'));
    }

    #[Route('/{ref}/payout', name: 'payment.payout', methods: ['POST', 'GET'])]
    public function payout(string $ref, EntityManagerInterface $entityManager, Request $request): Response
    {   
        
        $collecte = $entityManager->getRepository(Collecte::class)->findOneBy(['reference' => $ref]);
        $order = $entityManager->getRepository(Order::class)->findOneBy(['reference' => $ref]);

        $isOrder = false;
        $isCollecte = false;

        if($order){
            $isOrder = true;
        }

        if($collecte){
            $isCollecte = true;
        }

        if($isCollecte && $isOrder){
            $this->addFlash('error', "Une erreur c'est produit, Veuillez reessayer plus tard; si le probleme persiste merci de contacter le support.");
            $this->redirect($request->headers->get('referer'));
        }

        return $this->render('payment/payout.html.twig', [
            'order' => $order,
            'collecte' => $collecte
        ]);
    }

    #[Route('/{ref}/callback', name: 'payment.callback', methods: ['POST', 'GET'])]
    public function callback(Request $request, PayOutService $payOutService, EntityManagerInterface $entityManager, int $ref): Response
    {
        $transaction_id = $request->query->get('transaction_id');

        $order = $entityManager->getRepository(Order::class)->findOneByReference(['reference' => $ref]);
        $collecte = $entityManager->getRepository(Collecte::class)->findOneByReference(['reference' => $ref]);
        
        if($transaction_id){
            $confirmation = $payOutService->kkiaPayOut()->verifyTransaction($transaction_id);

            if($confirmation->status === "SUCCESS"){
                if($order){
                    return $this->redirectToRoute('order.onlineCheckout', ['order' => $order->getId(), "transaction" => $transaction_id, "methode" => $confirmation->source]);
                }elseif ($collecte) {
                    return $this->redirectToRoute('collecte.onlineCheckout', ['collecte' => $collecte->getId(), "transaction" => $transaction_id, "methode" => $confirmation->source]);
                }
            }else{
                dd($confirmation);
            }
        }

        return $this->render('home/index.html.twig');
    }
}
