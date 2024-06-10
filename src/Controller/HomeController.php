<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\ProductRepository;
use App\Services\QrCodeGenerator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function index(ProductRepository $productRepository, Request $request): Response
    {
        $user = $this->getUser();

        if ($user instanceof User){
            if($user->getShop() == null and $user->getJob() == null or $user->getJob() != null and $user->getJob()->isActive() != true){
                return $this->render('home/notEmployedHome.html.twig');
            }else {
                return $this->render('home/fullyAuthHome.html.twig');
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
