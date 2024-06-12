<?php

namespace App\Controller;

use App\Entity\Collecte;
use App\Entity\Order;
use App\Entity\User;
use App\Repository\CollecteRepository;
use App\Repository\OrderRepository;
use App\Repository\ProductRepository;
use App\Services\CalculatorService;
use App\Services\ChartByWeekService;
use App\Services\QrCodeGenerator;
use App\Services\TransactionChartBuilder;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;

class HomeController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function index(ChartBuilderInterface $chartBuilder, EntityManagerInterface $entityManager, CalculatorService $calculatorService, Request $request): Response
    {
        $user = $this->getUser();

        if ($user instanceof User){
            if($user->getShop() == null and $user->getJob() == null or $user->getJob() != null and $user->getJob()->isActive() != true){
                return $this->render('home/notEmployedHome.html.twig');
            }else {
                // Verifier si l'utilisateur connecté est le propriétaire du shop
                if($user->getShop() != null){
                    $shop = $user->getShop();
                }

                // Verifier si l'utilisateur connecté est un collecteur & livreur, caissier ou un laveur (un employé)
                if($user->getJob() != null){
                    $shop = $user->getJob()->getShop();
                }

                $chartByWeekService = new ChartByWeekService($entityManager,$shop);

                $weeklyOrders = $chartByWeekService->getOrderByWeek();
                $weeklyCollectes = $chartByWeekService->getCollecteByWeek();

                $newCollectes = $entityManager->getRepository(Collecte::class)->getNewCollectes($shop);
                $newOrders = $entityManager->getRepository(Order::class)->getNewOrders($shop);

                $transactionChartBuilder = new TransactionChartBuilder();

                $orderChartBuider = $chartBuilder->createChart(Chart::TYPE_LINE);
                $collecteChartBuider = $chartBuilder->createChart(Chart::TYPE_LINE);

                $orderChart = $transactionChartBuilder->build($orderChartBuider, $weeklyOrders, "Nombre de commandes par jour");

                $collecteChart = $transactionChartBuilder->build($collecteChartBuider, $weeklyCollectes, "Nombre de collectes par jour");

                $orderLast7DaysAmount = $weeklyOrders['totalAmountLast7Days'];
                $collecteLast7DaysAmount = $weeklyCollectes['totalAmountLast7Days'];

                $orderAmountPriviousLast7Days = $weeklyOrders['totalAmountPrevious7Days'];
                $collecteAmountPreviousLast7Days = $weeklyCollectes['totalAmountPrevious7Days'];

                $totaleTransactionsAmount = $orderLast7DaysAmount + $collecteLast7DaysAmount;

                $totalTransactionPreviousLast7days = $orderAmountPriviousLast7Days + $collecteAmountPreviousLast7Days;

                $transactionPercentageByPreviousAnLast7Dys = $calculatorService->percentage($totaleTransactionsAmount, $totalTransactionPreviousLast7days);
                
                $OrderPercentageTransaction = $calculatorService->percentage($orderLast7DaysAmount, $orderAmountPriviousLast7Days);
        
                $CollectePercentageTransaction = $calculatorService->percentage($collecteLast7DaysAmount, $collecteAmountPreviousLast7Days);

                return $this->render('home/fullyAuthHome.html.twig', [
                    'orderChart' => $orderChart,
                    'collecteChart' => $collecteChart,
                    'orderLast7DaysAmount' => $orderLast7DaysAmount,
                    'collecteLast7DaysAmount' => $collecteLast7DaysAmount,
                    'totaleTransactionsAmount' => $totaleTransactionsAmount,
                    'OrderPercentageTransaction' => round($OrderPercentageTransaction),
                    'CollectePercentageTransaction' => round($CollectePercentageTransaction),
                    'collecteAmountPreviousLast7Days' => $collecteAmountPreviousLast7Days,
                    'orderAmountPriviousLast7Days' => $orderAmountPriviousLast7Days,
                    'totalTransactionPreviousLast7days' => $totalTransactionPreviousLast7days,
                    'transactionPercentageByPreviousAnLast7Dys' => round($transactionPercentageByPreviousAnLast7Dys),
                    'newCollectes' => $newCollectes,
                    'newOrders' => $newOrders
                ]);
            }
        }

        $form = $this->createFormBuilder()->add('telefone', null, ['attr' => ['placeholder' => 'e.x : 7xxxxxxx']])->getForm();
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $telephone = $form->get('telefone')->getData();

            $request->getSession()->set('customerPhone', $telephone);

            return $this->redirectToRoute('customer.collecte');
        }

        $baseurl = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath();
        $url = $baseurl.$this->generateUrl('customer.collecte');
        $qrCodeGenerator = new QrCodeGenerator();
        $qrCode = $qrCodeGenerator->generateQrCode($url);
        return $this->render('home/index.html.twig', [
            'form' => $form,
            'qrcode' => $qrCode
        ]);
    }
}
