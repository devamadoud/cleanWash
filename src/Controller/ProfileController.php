<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/profile')]
class ProfileController extends AbstractController
{

    #[Route('/{user}', name: 'profile.index', methods: ['GET'])]
    public function index(User $user): Response
    {
        $connectedUser = $this->getUser();

        if($connectedUser instanceof User and $user != $connectedUser){
            return $this->redirectToRoute("profile.index", ['user' => $connectedUser->getId()]);
        }

        return $this->render('profile/index.html.twig', [
            'user' => $user,
        ]);
    }
}
