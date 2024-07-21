<?php

namespace App\Controller;

use App\Data\shipFilterData;
use App\Entity\Invoice;
use App\Entity\User;
use App\Form\InvoiceFilterType;
use App\Form\ShipeFilterType;
use App\Repository\InvoiceRepository;
use App\Services\NumberToWord;
use App\Services\PdfGenerator;
use App\Services\QrCodeGenerator;
use App\Services\UserProvider;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[Route('/invoice')]
class InvoiceController extends AbstractController
{
    #[Route('/', name: 'invoice.index')]
    public function index(InvoiceRepository $invoiceRepository, UserProvider $userProvider, Request $request): Response
    {   

        if($request->query->get('reset')){
            return $this->redirectToRoute('invoice.index');
        }

        $user = $this->getUser();
        
        if (!($user instanceof User)) {
            return $this->redirectToRoute('app_login');
        }

        $filterData = new shipFilterData();
        
        $filterData->shop = $userProvider->getShop($user);

        $filterData->page = $request->query->get('page', 1);

        $filterForm = $this->createForm(InvoiceFilterType::class, $filterData);
        $filterForm->handleRequest($request);
        $invoices = $invoiceRepository->getByShop($filterData);
        
        return $this->render('invoice/index.html.twig', [
            'invoices' => $invoices,
            'filterForm' => $filterForm
        ]);
    }

    #[Route('/{invoice}', name: 'invoice.show')]
    public function show(Invoice $invoice, PdfGenerator $pdfGenerator, NumberToWord $numberToWord, UserProvider $userProvider, Request $request, QrCodeGenerator $qrCodeGenerator, UrlGeneratorInterface $urlGenerator): Response
    {
        $logoData = file_get_contents('images/icons/setsetalDesktopPurple.png');
        $base64Logo = "data:image/png;base64,".base64_encode($logoData);

        $baseUrl = $request->getSchemeAndHttpHost();
        $transactionUrl = $baseUrl;
        if($invoice->getType() == 'collecte') {
            $transactionUrl .= $urlGenerator->generate('collecte.show', ['id' => $invoice->getCollecte()->getId()]);
        }
        if($invoice->getType() == 'order') {
            $transactionUrl .= $urlGenerator->generate('order.show', ['id' => $invoice->getOrderInvoice()->getId()]);
        }

        $qrCode = $qrCodeGenerator->generateQrCode($transactionUrl);

        return $this->render('invoice/show.html.twig', [
            'invoice' => $invoice,
            'qrCode' => $qrCode,
            'logo' => $base64Logo
        ]);
    }

    #[Route('/download/{invoice}', name: 'invoice.download')]
    public function download(Invoice $invoice, PdfGenerator $pdfGenerator, QrCodeGenerator $qrCodeGenerator, Request $request, UrlGeneratorInterface $urlGenerator): Response
    {
        $logoData = file_get_contents('images/icons/setsetalDesktopPurple.png');
        $base64Logo = "data:image/png;base64,".base64_encode($logoData);

        $baseUrl = $request->getSchemeAndHttpHost();
        $transactionUrl = $baseUrl;
        if($invoice->getType() == 'collecte') {
            $transactionUrl .= $urlGenerator->generate('collecte.show', ['id' => $invoice->getCollecte()->getId()]);
        }
        if($invoice->getType() == 'order') {
            $transactionUrl .= $urlGenerator->generate('order.show', ['id' => $invoice->getOrderInvoice()->getId()]);
        }

        $qrCode = $qrCodeGenerator->generateQrCode($transactionUrl);

        $html = $this->renderView('invoice/_pdf_invoice.html.twig', [
            'invoice' => $invoice,
            'qrCode' => $qrCode,
            'logo' => $base64Logo
        ]);

        $domPdf = $pdfGenerator->generate($html);

        $pdfName = $invoice->getType().'_'.$invoice->getId().'.pdf';

        $response = new Response($domPdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$pdfName.'"',
        ]);

        return $response;
    }
}
