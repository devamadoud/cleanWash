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
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/payment')]
class PaymentController extends AbstractController
{
    #[Route('/', name: 'payment.index', methods: ['POST', 'GET'])]
    public function index(Request $request, EntityManagerInterface $entityManager, PaymentRepository $paymentRepository)
    {
        $user = $this->getUser();

        if(!($user instanceof User)){
            $this->addFlash('error', "Vous devez vous identifier pour continuer !");
            return $this->redirectToRoute('app_login');
        }

        if($user->getShop()){
            $shop = $user->getShop();
        }

        if($user->getJob() and $user->getJob()->getPoste() == "caissier"){
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

        if($user->getJob() and $user->getJob()){
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
        $ref = $request->request->get('ref');
        $type = $request->request->get('type');
        $paymentMethode = $request->request->get('paymentMethode');

        if($ref){
            if($type && $type == "collecte"){

                $collecte = $entityManager->getRepository(Collecte::class)->findOneBy(['reference' => $ref]);

                if($collecte){
                    return $this->redirectToRoute('collecte.checkout', ['collecte' => $collecte->getId()]);
                }
            }

            if($ref && $type == "order"){

                $order = $entityManager->getRepository(Order::class)->findOneBy(['reference' => $ref]);

                if($order){
                    $request->getSession()->set('paymentMethode', $paymentMethode);
                    return $this->redirectToRoute('order.checkout', ['order' => $order->getId()]);
                }
            }
        }
        
        $this->addFlash("error", "Une erreur est survenue.");
        return $this->redirectToRoute('home');
    }

    #[Route('/{order}/payout', name: 'payment.payout', methods: ['POST', 'GET'])]
    public function payout(Order $order, EntityManagerInterface $entityManager): Response
    {
        return $this->render('payment/payout.html.twig', [
            'order' => $order
        ]);
    }

    #[Route('/{order}/callback', name: 'payment.callback', methods: ['POST', 'GET'])]
    public function callback(Request $request, PayOutService $payOutService, int $order): Response
    {
        $transaction_id = $request->query->get('transaction_id');
        if($transaction_id){
            $confirmation = $payOutService->kkiaPayOut()->verifyTransaction($transaction_id);

            if($confirmation->status === "SUCCESS"){
                return $this->redirectToRoute('order.onlineCheckout', ['order' => $order, "transaction" => $transaction_id, "methode" => $confirmation->source]);
            }else{
                dd($confirmation);
            }
        }

        return $this->render('home/index.html.twig');
    }
}
