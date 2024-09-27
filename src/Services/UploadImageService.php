<?php

namespace App\Services;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile as FileUploadedFile;

class UploadImageService extends AbstractController
{

    public function __construct(private string $uploadDir)
    {
        $this->uploadDir = $uploadDir;
    }

    public function uploadImage(FileUploadedFile $imageFile, string $endPath): array
    {
        $imagePath = $imageFile->getPathName();
        $imageExt = $imageFile->guessExtension();
        $size = $imageFile->getSize();

        $error = '0';
        // On ne pend en charge que les images jpg et png
        if($imageExt !== 'jpg' && $imageExt !== 'png') {
            $error = "L'image doit être au format jpg ou png";
            return $error;
        }

        // On verifie si la taille de l'image ne dépasse pas 2Mo
        if($size > 2000000) {
            $error = "La taille de l\'image ne peut pas dépasser 2Mo";
            return $error;
        }

        $imageName = uniqid().random_int(100000, 999999).'.'.$imageExt;

        $imagePath = $this->uploadDir.$endPath;
        $imageFile->move($imagePath, $imageName);

        $response = ['error' => $error, 'imageName' => $imageName];
        return $response;
    }

    public function deleteImage(string $imageName, string $endPath): void
    {
        $imagePath = $this->uploadDir.$endPath.$imageName;
        if(file_exists($imagePath)) {
            unlink($imagePath);
        }
    }
}