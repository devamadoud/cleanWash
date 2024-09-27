<?php

namespace App\Controller;

use App\Data\collecteSearchData;
use App\Data\shipFilterData;
use App\Entity\Shipe;
use App\Form\CollecteFilterType;
use App\Form\ShipeFilterType;
use App\Repository\ShipeRepository;
use App\Services\UserProvider;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/shipe')]
class ShipeController extends AbstractController
{
    #[Route('/', name: 'shipe.index')]
    public function index(UserProvider $userProvider, Request $request, ShipeRepository $shipeRepository): Response
    {
        $user = $userProvider->connectedUser($this->getUser());

        if(!$user){
            $this->addFlash("error", "Vous devez vous identifier pour accéder à la liste des livraisons.");
            return $this->redirectToRoute("app_login");
        }


        $reset = $request->query->get('reset', null);
        if($reset){
            return $this->redirectToRoute('shipe.index');
        }

        $shipFilterData = new shipFilterData();

        $shipFilterData->shop = $userProvider->getShop($user);

        $shipFilterData->page = $request->query->getInt('page', 1);
        $orderFormFilter = $this->createForm(ShipeFilterType::class, $shipFilterData);
        $orderFormFilter->handleRequest($request);

        $shipes = $shipeRepository->findBySearch($shipFilterData);

        return $this->render('shipe/index.html.twig', [
            'shipes' => $shipes,
            'filterForm' => $orderFormFilter,
        ]);
    }

    #[Route('/{id}/show', name: 'shipe.show', methods: ['GET'])]
    public function show(Shipe $shipe): Response
    {
        return $this->render('shipe/show.html.twig', [
            'shipe' => $shipe,
        ]);
    }
}
