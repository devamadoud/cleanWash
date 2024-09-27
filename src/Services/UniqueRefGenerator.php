<?php

namespace App\Services;

use App\Entity\Collecte;
use App\Entity\CollecteDetailles;
use App\Entity\Order;
use App\Entity\OrderDetailles;
use Doctrine\ORM\EntityManagerInterface;

class UniqueRefGenerator
{
    private EntityManagerInterface $em;
     public function __construct(EntityManagerInterface $em){
        $this->em = $em;
    }

    public function createUniqueNumber(int $n): string
    {
        // $n est le nombre de chiffre que l'on souhaite retourner après avoir généré les chiffres aléatoires

        $randomBytes = random_bytes(6); // Génère 6 octets de manière sécurisée
        $randomNumber = hexdec(bin2hex($randomBytes)); // Convertit les octets en un nombre décimal

        // Obtient une suite de 10 chiffres à partir du nombre généré
        $length = strlen(strval($randomNumber));
        $desiredLength = $n;

        // On vérifie si le nombre généré est inférieur au nombre de chiffre souhaité
        if($length < $desiredLength){
            // Si oui, on génère un nouveau nombre
            return $this->createUniqueNumber($n);
        }

        // On génére un nombre aleatoire de départ
        $digiStartIdex = rand(0, $length - $desiredLength);

        // On retourne le nombre généré
        $digitSequence = substr(strval($randomNumber), strval($digiStartIdex), strval($desiredLength));

        return $digitSequence;
    }

    public function generateCollecteDetaillesReference(int $n = 6): string
    {
        // $n est le nombre de chiffre que l'on souhaite retourner après avoir généré les chiffres aléatoires
        
        // On récupère le nom et le prénom de l'utilisateur
        // $fullName = strtolower($fullName);

        // On récupère la première lettre du nom et du prénom
        // $firstLetterFullName = substr($fullName, 0, 1);

        // On génère un nombre unique pour le compte d'un guichet
        $collecteReference = $this->createUniqueNumber($n);
        
        // On génère la reference
        // $reference = $fullName.$collecteReference;

        // On vérifie si le nombre généré est unique
        $numberExists = $this->em->getRepository(CollecteDetailles::class)->findOneByReference($collecteReference);
        
        if($numberExists){
            // Si le nombre généré existe déjà, on génère un nouveau nombre
            return $this->generateCollecteDetaillesReference($n);
        }

        // On retune le bon numero
        return $collecteReference;
    }

    public function generateCollecteReference(int $n = 6): string
    {
        // $n est le nombre de chiffre que l'on souhaite retourner après avoir généré les chiffres aléatoires
        
        // On récupère le nom et le prénom de l'utilisateur
        // $fullName = strtolower($fullName);

        // On récupère la première lettre du nom et du prénom
        // $firstLetterFullName = substr($fullName, 0, 1);

        // On génère un nombre unique pour le compte d'un guichet
        $collecteReference = $this->createUniqueNumber($n);
        
        // On génère la reference
        // $reference = $fullName.$collecteReference;

        // On vérifie si le nombre généré est unique
        $numberExists = $this->em->getRepository(Collecte::class)->findOneByReference($collecteReference);
        
        if($numberExists){
            // Si le nombre généré existe déjà, on génère un nouveau nombre
            return $this->generateCollecteReference($n);
        }

        // On retune le bon numero
        return $collecteReference;
    }

    public function generateOrderDetaillesReference(int $n = 6): string
    {
        // $n est le nombre de chiffre que l'on souhaite retourner après avoir généré les chiffres aléatoires
        
        // On récupère le nom et le prénom de l'utilisateur
        // $fullName = strtolower($fullName);

        // On récupère la première lettre du nom et du prénom
        // $firstLetterFullName = substr($fullName, 0, 1);

        // On génère un nombre unique pour le compte d'un guichet
        $orderReference = $this->createUniqueNumber($n);
        
        // On génère la reference
        // $reference = $fullName.$collecteReference;

        // On vérifie si le nombre généré est unique
        $numberExists = $this->em->getRepository(OrderDetailles::class)->findOneByReference($orderReference);
        
        if($numberExists){
            // Si le nombre généré existe déjà, on génère un nouveau nombre
            return $this->generateOrderDetaillesReference($n);
        }

        // On retune le bon numero
        return $orderReference;
    }

    public function generateOrderReference(int $n = 6): string
    {
        // $n est le nombre de chiffre que l'on souhaite retourner après avoir généré les chiffres aléatoires
        
        // On récupère le nom et le prénom de l'utilisateur
        // $fullName = strtolower($fullName);

        // On récupère la première lettre du nom et du prénom
        // $firstLetterFullName = substr($fullName, 0, 1);

        // On génère un nombre unique pour le compte d'un guichet
        $orderReference = $this->createUniqueNumber($n);
        
        // On génère la reference
        // $reference = $fullName.$collecteReference;

        // On vérifie si le nombre généré est unique
        $numberExists = $this->em->getRepository(Order::class)->findOneByReference($orderReference);
        
        if($numberExists){
            // Si le nombre généré existe déjà, on génère un nouveau nombre
            return $this->generateOrderReference($n);
        }

        // On retune le bon numero
        return $orderReference;
    }
}