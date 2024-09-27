<?php

namespace App\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;

class StringToFileTransformer implements DataTransformerInterface
{
    private $uploadsDir;

    public function __construct(string $uploadsDir)
    {
        $this->uploadsDir = $uploadsDir;
    }

    public function transform(mixed $value): mixed
    {
        if (null === $value || $value === '') {
            return null; // Retourne null si la valeur est nulle ou vide
        }

        // Assurez-vous que $value est une chaîne avant d'essayer de la convertir
        if (is_string($value)) {
            $filePath = $this->uploadsDir . '/' . $value;

            try {
                return new File($filePath);
            } catch (FileNotFoundException $e) {
                return null; // Si le fichier n'existe pas, retourner null
            }
        }

        return $value; // Retourne la valeur telle quelle si ce n'est pas une chaîne
    }

    public function reverseTransform(mixed $value): mixed
    {
        if (null === $value) {
            return null; // Retourne null si la valeur est nulle
        }

        // Si l'objet File est valide, on retourne le nom du fichier
        if ($value instanceof File) {
            return $value->getFilename();
        }

        return $value; // Retourne la chaîne de caractères si c'est déjà un nom de fichier
    }
}