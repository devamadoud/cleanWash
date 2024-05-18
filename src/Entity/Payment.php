<?php

namespace App\Entity;

use App\Repository\PaymentRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PaymentRepository::class)]
class Payment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(inversedBy: 'payment', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Collecte $collecte = null;

    #[ORM\Column]
    private ?float $amount = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $paidAt = null;

    #[ORM\Column(length: 255)]
    private ?string $paimentMode = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCollecte(): ?Collecte
    {
        return $this->collecte;
    }

    public function setCollecte(Collecte $collecte): static
    {
        $this->collecte = $collecte;

        return $this;
    }

    public function getAmount(): ?float
    {
        return $this->amount;
    }

    public function setAmount(float $amount): static
    {
        $this->amount = $amount;

        return $this;
    }

    public function getPaidAt(): ?\DateTimeImmutable
    {
        return $this->paidAt;
    }

    public function setPaidAt(\DateTimeImmutable $paidAt): static
    {
        $this->paidAt = $paidAt;

        return $this;
    }

    public function getPaimentMode(): ?string
    {
        return $this->paimentMode;
    }

    public function setPaimentMode(string $paimentMode): static
    {
        $this->paimentMode = $paimentMode;

        return $this;
    }
}
