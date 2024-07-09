<?php
namespace App\Services;

use App\Entity\Collecte;
use App\Entity\Order;
use App\Entity\Shipe;
use DateTimeImmutable;
use Symfony\Component\Security\Core\User\UserInterface;

class ShipeService
{
    public function ship(Collecte|Order $transaction, string $status, UserInterface $user): Shipe|null
    {
        $shipe = $transaction->getShipe();

        if(!$shipe){
            $shipe = new Shipe();
            $userProvider = new UserProvider();

            if($transaction instanceof Collecte){
                $shipe->setCollecte($transaction)
                    ->setType("Collecte de pressing")
                ;
            }

            if($transaction instanceof Order){
                $shipe->setProductOrder($transaction)
                    ->setType("Commande de produit")
                ;
            }

            $shipe->setShippedAt(new DateTimeImmutable())
                ->setShippedBy($userProvider->connectedUser($user))
                ->setStatus($status)
                ->setShop($userProvider->getShop($user))
                ->setCustomer($transaction->getCustomer())
            ;
        }

        if(!($shipe instanceof Shipe)){
            return null;
        }

        $shipe->setStatus($status);

        return $shipe;
    }
}