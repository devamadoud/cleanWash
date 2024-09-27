<?php

namespace App\Controller;

use App\Data\searchData;
use App\Form\SearchFormType;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/boutique')]
class BoutiqueController extends AbstractController
{
    #[Route('/', name: 'boutique')]
    public function index(ProductRepository $productRepository, Request $request): Response
    {
        $dataSearch = new searchData();
        $dataSearch->page = $request->get('page', 1);
        $dataSearch->category = $request->get('category', null);
        
        $sarchForm = $this->createForm(SearchFormType::class, $dataSearch);
        $sarchForm->handleRequest($request);

        $products = $productRepository->findBySearch($sarchForm->getData());

        return $this->render('boutique/index.html.twig', [
            'products' => $products,
            'searchForm' => $sarchForm
        ]);
    }
}
