<?php

namespace App\Security\Voter;

use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class CollecteVoter extends Voter
{
    public const CREATE = 'COLLECTE_CREATE';
    public const EDIT = 'COLLECTE_EDIT';
    public const VIEW = 'COLLECTE_VIEW';
    public const LIST = 'COLLECTE_LIST';
    public const CONFIRME = 'COLLECTE_CONFIRME';

    protected function supports(string $attribute, mixed $subject): bool
    {
        // replace with your own logic
        // https://symfony.com/doc/current/security/voters.html
        return 
            in_array($attribute, [self::CREATE, self::LIST, self::CONFIRME]) ||
            (
                in_array($attribute, [self::EDIT, self::VIEW])
                && $subject instanceof \App\Entity\Collecte
            );
    }

    /**
    * @param Collecte|null $subject
    */
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        $collecte = $subject;

        // ... (check conditions and return true to grant permission) ...
        switch ($attribute) {

            case self::EDIT:
                if($collecte->getStatus() == "En attente"){
                    return true;
                }

                if($collecte->getStatus() != "En attente" and $user instanceof User){
                    return true;
                }
                
                break;

            case self::VIEW:
                if($user instanceof User){
                    return $user->getShop() == $collecte->getShop()
                    or $user->getJob()->getShop() == $collecte->getShop() and $user->getJob()->getPoste() == "collecteur"
                    or $user->getJob()->getShop() == $collecte->getShop() and $user->getJob()->getPoste() == "caissier";
                }
                break;

            case self::CONFIRME:
                if($user instanceof User){
                    return $user->getShop() == $collecte->getShop();
                }
                break;
            case self::LIST:
                if($user instanceof User){
                    return $user->getShop()
                    or $user->getJob()->isActive() == true;
                }
            case self::CREATE:
                return true;
                break;
        }

        return false;
    }
}
