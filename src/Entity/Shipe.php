<?php

namespace App\Entity;

use App\Repository\ShipeRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ShipeRepository::class)]
class Shipe
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $shippedAt = null;

    #[ORM\ManyToOne(inversedBy: 'shipes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $shippedBy = null;

    #[ORM\OneToOne(inversedBy: 'shipe', cascade: ['persist', 'remove'])]
    private ?Collecte $collecte = null;

    #[ORM\OneToOne(inversedBy: 'shipe', cascade: ['persist', 'remove'])]
    private ?Order $productOrder = null;

    #[ORM\Column(length: 255)]
    private ?string $status = null;

    #[ORM\Column(length: 255)]
    private ?string $type = null;

    #[ORM\ManyToOne(inversedBy: 'shipes')]
    private ?Shop $shop = null;

    #[ORM\ManyToOne(inversedBy: 'shipes')]
    private ?Customer $customer = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getShippedAt(): ?\DateTimeImmutable
    {
        return $this->shippedAt;
    }

    public function setShippedAt(\DateTimeImmutable $shippedAt): static
    {
        $this->shippedAt = $shippedAt;

        return $this;
    }

    public function getShippedBy(): ?User
    {
        return $this->shippedBy;
    }

    public function setShippedBy(?User $shippedBy): static
    {
        $this->shippedBy = $shippedBy;

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

    public function getProductOrder(): ?Order
    {
        return $this->productOrder;
    }

    public function setProductOrder(?Order $productOrder): static
    {
        $this->productOrder = $productOrder;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getShop(): ?Shop
    {
        return $this->shop;
    }

    public function setShop(?Shop $shop): static
    {
        $this->shop = $shop;

        return $this;
    }

    public function getCustomer(): ?Customer
    {
        return $this->customer;
    }

    public function setCustomer(?Customer $customer): static
    {
        $this->customer = $customer;

        return $this;
    }
}
