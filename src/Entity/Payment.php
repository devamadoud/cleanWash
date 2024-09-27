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

    #[ORM\Column]
    private ?float $amount = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $paidAt = null;

    #[ORM\Column(length: 255)]
    private ?string $paimentMode = null;

    #[ORM\OneToOne(mappedBy: 'payment', cascade: ['persist', 'remove'])]
    private ?Collecte $collecte = null;

    #[ORM\Column]
    private ?bool $confirmation = null;

    #[ORM\Column(length: 255)]
    private ?string $cashedBy = null;

    #[ORM\OneToOne(mappedBy: 'payment', cascade: ['persist', 'remove'])]
    private ?Order $productOrder = null;

    #[ORM\Column(length: 255)]
    private ?string $status = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $transactionId = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $type = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getCollecte(): ?Collecte
    {
        return $this->collecte;
    }

    public function setCollecte(?Collecte $collecte): static
    {
        // unset the owning side of the relation if necessary
        if ($collecte === null && $this->collecte !== null) {
            $this->collecte->setPayment(null);
        }

        // set the owning side of the relation if necessary
        if ($collecte !== null && $collecte->getPayment() !== $this) {
            $collecte->setPayment($this);
        }

        $this->collecte = $collecte;

        return $this;
    }

    public function isConfirmation(): ?bool
    {
        return $this->confirmation;
    }

    public function setConfirmation(bool $confirmation): static
    {
        $this->confirmation = $confirmation;

        return $this;
    }

    public function getCashedBy(): ?string
    {
        return $this->cashedBy;
    }

    public function setCashedBy(string $cashedBy): static
    {
        $this->cashedBy = $cashedBy;

        return $this;
    }

    public function getProductOrder(): ?Order
    {
        return $this->productOrder;
    }

    public function setProductOrder(?Order $productOrder): static
    {
        // unset the owning side of the relation if necessary
        if ($productOrder === null && $this->productOrder !== null) {
            $this->productOrder->setPayment(null);
        }

        // set the owning side of the relation if necessary
        if ($productOrder !== null && $productOrder->getPayment() !== $this) {
            $productOrder->setPayment($this);
        }

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

    public function getTransactionId(): ?string
    {
        return $this->transactionId;
    }

    public function setTransactionId(?string $transactionId): static
    {
        $this->transactionId = $transactionId;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): static
    {
        $this->type = $type;

        return $this;
    }
}
