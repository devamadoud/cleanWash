<?php

namespace App\Security\Voter;

use App\Entity\Employe;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class EmployeVoter extends Voter
{
    public const EDIT = 'EMPLOYE_EDIT';
    public const VIEW = 'EMPLOYE_VIEW';
    public const CREATE = 'EMPLOYE_CREATE';
    public const LIST = 'EMPLOYE_LIST';
    public const REVOKE = 'EMPLOYE_REVOKE';

    protected function supports(string $attribute, mixed $subject): bool
    {
        // replace with your own logic
        // https://symfony.com/doc/current/security/voters.html
        return 
            in_array($attribute, [self::CREATE, self::LIST, self::REVOKE]) ||
            (
                in_array($attribute, [self::EDIT, self::VIEW])
                && $subject instanceof \App\Entity\Employe
            );
    }

    /**
    * @param Employe|null $subject
    */
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        // if the user is anonymous, do not grant access
        if (!$user instanceof User) {
            return false;
        }

        $employe = $subject;

        // ... (check conditions and return true to grant permission) ...
        switch ($attribute) {
            case self::EDIT:
                return $user->getShop() == $employe->getShop();
                break;

            case self::VIEW:
            case self::LIST:
                return $user->getShop() != null;
                break;

            case self::CREATE:
                return $user->getShop() and $user->getShop()->getEmployes()->count() <= 3;
                break;

            case self::REVOKE:
                return $user->getShop() == $employe->getShop();
                break;
        }

        return false;
    }
}
