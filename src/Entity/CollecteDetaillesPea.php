<?php

namespace App\Entity;

use App\Repository\CollecteDetaillesPeaRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CollecteDetaillesPeaRepository::class)]
class CollecteDetaillesPea
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $pea = null;

    #[ORM\ManyToOne(inversedBy: 'collecteDetaillesPeas', cascade: ['persist'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Collecte $collecte = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true)]
    private ?ClothingPea $clothingPea = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $status = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPea(): ?int
    {
        return $this->pea;
    }

    public function setPea(int $pea): static
    {
        $this->pea = $pea;

        return $this;
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

    public function getClothingPea(): ?ClothingPea
    {
        return $this->clothingPea;
    }

    public function setClothingPea(?ClothingPea $clothingPea): static
    {
        $this->clothingPea = $clothingPea;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(?string $status): static
    {
        $this->status = $status;

        return $this;
    }

}
