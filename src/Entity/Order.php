<?php

namespace App\Entity;

use App\Repository\OrderRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OrderRepository::class)]
#[ORM\Table(name: '`order`')]
class Order
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column(length: 255)]
    private ?string $status = null;

    #[ORM\ManyToOne(inversedBy: 'orders', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Customer $customer = null;

    #[ORM\Column]
    private ?float $totale = null;

    /**
     * @var Collection<int, OrderDetailles>
     */
    #[ORM\OneToMany(targetEntity: OrderDetailles::class, mappedBy: 'theOrder', orphanRemoval: true, cascade: ['persist'])]
    private Collection $orderDetailles;

    #[ORM\ManyToOne(inversedBy: 'orders', cascade: ['persist', 'remove'])]
    private ?Shop $shop = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $reference = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $paidAt = null;

    #[ORM\OneToOne(inversedBy: 'productOrder', cascade: ['persist', 'remove'])]
    private ?Payment $payment = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $paymentChoice = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $secret = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $canceledAt = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $motifCancel = null;

    #[ORM\OneToOne(mappedBy: 'productOrder', cascade: ['persist', 'remove'])]
    private ?Shipe $shipe = null;

    #[ORM\OneToOne(mappedBy: 'orderInvoice', cascade: ['persist', 'remove'])]
    private ?Invoice $invoice = null;

    public function __construct()
    {
        $this->orderDetailles = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

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

    public function getCustomer(): ?Customer
    {
        return $this->customer;
    }

    public function setCustomer(?Customer $customer): static
    {
        $this->customer = $customer;

        return $this;
    }

    public function getTotale(): ?float
    {
        return $this->totale;
    }

    public function setTotale(float $totale): static
    {
        $this->totale = $totale;

        return $this;
    }

    /**
     * @return Collection<int, OrderDetailles>
     */
    public function getOrderDetailles(): Collection
    {
        return $this->orderDetailles;
    }

    public function addOrderDetaille(OrderDetailles $orderDetaille): static
    {
        if (!$this->orderDetailles->contains($orderDetaille)) {
            $this->orderDetailles->add($orderDetaille);
            $orderDetaille->setTheOrder($this);
        }

        return $this;
    }

    public function removeOrderDetaille(OrderDetailles $orderDetaille): static
    {
        if ($this->orderDetailles->removeElement($orderDetaille)) {
            // set the owning side to null (unless already changed)
            if ($orderDetaille->getTheOrder() === $this) {
                $orderDetaille->setTheOrder(null);
            }
        }

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

    public function __toString(): string
    {
        return "Order entity". $this->id;
    }

    public function getReference(): ?string
    {
        return $this->reference;
    }

    public function setReference(?string $reference): static
    {
        $this->reference = $reference;

        return $this;
    }

    public function getPaidAt(): ?\DateTimeImmutable
    {
        return $this->paidAt;
    }

    public function setPaidAt(?\DateTimeImmutable $paidAt): static
    {
        $this->paidAt = $paidAt;

        return $this;
    }

    public function getPayment(): ?Payment
    {
        return $this->payment;
    }

    public function setPayment(?Payment $payment): static
    {
        $this->payment = $payment;

        return $this;
    }

    public function getPaymentChoice(): ?string
    {
        return $this->paymentChoice;
    }

    public function setPaymentChoice(?string $paymentChoice): static
    {
        $this->paymentChoice = $paymentChoice;

        return $this;
    }

    public function getSecret(): ?string
    {
        return $this->secret;
    }

    public function setSecret(?string $secret): static
    {
        $this->secret = $secret;

        return $this;
    }

    public function getCanceledAt(): ?\DateTimeImmutable
    {
        return $this->canceledAt;
    }

    public function setCanceledAt(?\DateTimeImmutable $canceledAt): static
    {
        $this->canceledAt = $canceledAt;

        return $this;
    }

    public function getMotifCancel(): ?string
    {
        return $this->motifCancel;
    }

    public function setMotifCancel(?string $motifCancel): static
    {
        $this->motifCancel = $motifCancel;

        return $this;
    }

    public function getShipe(): ?Shipe
    {
        return $this->shipe;
    }

    public function setShipe(?Shipe $shipe): static
    {
        // unset the owning side of the relation if necessary
        if ($shipe === null && $this->shipe !== null) {
            $this->shipe->setProductOrder(null);
        }

        // set the owning side of the relation if necessary
        if ($shipe !== null && $shipe->getProductOrder() !== $this) {
            $shipe->setProductOrder($this);
        }

        $this->shipe = $shipe;

        return $this;
    }

    public function getInvoice(): ?Invoice
    {
        return $this->invoice;
    }

    public function setInvoice(?Invoice $invoice): static
    {
        // unset the owning side of the relation if necessary
        if ($invoice === null && $this->invoice !== null) {
            $this->invoice->setOrderInvoice(null);
        }

        // set the owning side of the relation if necessary
        if ($invoice !== null && $invoice->getOrderInvoice() !== $this) {
            $invoice->setOrderInvoice($this);
        }

        $this->invoice = $invoice;

        return $this;
    }
}
