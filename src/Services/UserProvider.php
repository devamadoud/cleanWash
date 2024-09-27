<?php
namespace App\Services;

use App\Entity\Shop;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\User\UserInterface;

class UserProvider extends AbstractController
{
    public function getShop($user) : Shop|null
    {
        if(!($user instanceof User)){
            return null;
        }

        if($user->getShop()){
            return $user->getShop();
        }

        if($user->getJob()->getShop()){
            return $user->getJob()->getShop();
        }

        return null;
    }

    public function connectedUser(UserInterface $user) : User|null
    {
        if(!($user instanceof User)){
            return null;
        }

        return $user;
    }
}