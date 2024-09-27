<?php

namespace App\Entity;

use App\Repository\ClothingPeaRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ClothingPeaRepository::class)]
class ClothingPea
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?float $pea = null;

    #[ORM\Column]
    private ?float $priceMin = null;

    #[ORM\Column]
    private ?float $priceMax = null;

    #[ORM\Column]
    private ?float $peaMax = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPea(): ?float
    {
        return $this->pea;
    }

    public function setPea(float $pea): static
    {
        $this->pea = $pea;

        return $this;
    }

    public function getPriceMin(): ?float
    {
        return $this->priceMin;
    }

    public function setPriceMin(float $priceMin): static
    {
        $this->priceMin = $priceMin;

        return $this;
    }

    public function getPriceMax(): ?float
    {
        return $this->priceMax;
    }

    public function setPriceMax(float $priceMax): static
    {
        $this->priceMax = $priceMax;

        return $this;
    }

    public function getPeaMax(): ?float
    {
        return $this->peaMax;
    }

    public function setPeaMax(float $peaMax): static
    {
        $this->peaMax = $peaMax;

        return $this;
    }
}
