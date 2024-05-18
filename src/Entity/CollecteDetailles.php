<?php

namespace App\Entity;

use App\Repository\CollecteDetaillesRepository;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CollecteDetaillesRepository::class)]
class CollecteDetailles
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'collecteDetailles', cascade: ['persist'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Collecte $collecte = null;

    #[ORM\ManyToOne(inversedBy: 'collecteDetailles')]
    #[ORM\JoinColumn(nullable: false)]
    private ?ClothingType $clothingType = null;

    #[ORM\Column]
    #[Assert\NotBlank]
    #[Assert\Positive]
    private ?int $quantity = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCollecte(): ?Collecte
    {
        return $this->collecte;
    }

    public function setCollecte(?Collecte $collecte): static
    {
        $this->collecte = $collecte;

        return $this;
    }

    public function getClothingType(): ?ClothingType
    {
        return $this->clothingType;
    }

    public function setClothingType(?ClothingType $clothingType): static
    {
        $this->clothingType = $clothingType;

        return $this;
    }

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): static
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function __toString(): string
    {
        return $this->clothingType->getName();
    }
}
