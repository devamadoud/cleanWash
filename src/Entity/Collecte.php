<?php

namespace App\Entity;

use App\Repository\CollecteRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CollecteRepository::class)]
class Collecte
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'collectes', cascade: ['persist'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Customer $customer = null;

    #[ORM\ManyToOne(inversedBy: 'collectes', cascade: ['persist'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Shop $shop = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $collectedAt = null;

    #[ORM\Column(length: 255)]
    private ?string $status = null;

    /**
     * @var Collection<int, CollecteDetailles>
     */
    #[ORM\OneToMany(targetEntity: CollecteDetailles::class, mappedBy: 'collecte', orphanRemoval: true, cascade: ['persist'])]
    #[Assert\Valid]
    private Collection $collecteDetailles;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $paymentChoice = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $payedAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $secret = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $confirmedAt = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $confirmedBy = null;

    #[ORM\ManyToOne(inversedBy: 'collectes')]
    private ?User $collectedBy = null;

    /**
     * @var Collection<int, CollecteDetaillesPea>
     */
    #[ORM\OneToMany(targetEntity: CollecteDetaillesPea::class, mappedBy: 'collecte', orphanRemoval: true, cascade: ['persist'])]
    #[Assert\Valid]
    private Collection $collecteDetaillesPeas;

    #[ORM\Column(length: 255)]
    private ?string $collecteType = null;

    #[ORM\Column(length: 255)]
    private ?string $reference = null;

    #[ORM\OneToOne(inversedBy: 'collecte', cascade: ['persist', 'remove'])]
    private ?Payment $payment = null;

    #[ORM\Column(nullable: true)]
    private ?float $totale = null;

    public function __construct()
    {
        $this->collecteDetailles = new ArrayCollection();
        $this->collecteDetaillesPeas = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getShop(): ?Shop
    {
        return $this->shop;
    }

    public function setShop(?Shop $shop): static
    {
        $this->shop = $shop;

        return $this;
    }

    public function getCollectedAt(): ?\DateTimeImmutable
    {
        return $this->collectedAt;
    }

    public function setCollectedAt(\DateTimeImmutable $collectedAt): static
    {
        $this->collectedAt = $collectedAt;

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

    /**
     * @return Collection<int, CollecteDetailles>
     */
    public function getCollecteDetailles(): Collection
    {
        return $this->collecteDetailles;
    }

    public function addCollecteDetaille(CollecteDetailles $collecteDetaille): static
    {
        if (!$this->collecteDetailles->contains($collecteDetaille)) {
            $this->collecteDetailles->add($collecteDetaille);
            $collecteDetaille->setCollecte($this);
        }

        return $this;
    }

    public function removeCollecteDetaille(CollecteDetailles $collecteDetaille): static
    {
        if ($this->collecteDetailles->removeElement($collecteDetaille)) {
            // set the owning side to null (unless already changed)
            if ($collecteDetaille->getCollecte() === $this) {
                $collecteDetaille->setCollecte(null);
            }
        }

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

    public function getPayedAt(): ?\DateTimeImmutable
    {
        return $this->payedAt;
    }

    public function setPayedAt(?\DateTimeImmutable $payedAt): static
    {
        $this->payedAt = $payedAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

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

    public function getConfirmedAt(): ?\DateTimeImmutable
    {
        return $this->confirmedAt;
    }

    public function setConfirmedAt(?\DateTimeImmutable $confirmedAt): static
    {
        $this->confirmedAt = $confirmedAt;

        return $this;
    }

    public function getConfirmedBy(): ?string
    {
        return $this->confirmedBy;
    }

    public function setConfirmedBy(?string $confirmedBy): static
    {
        $this->confirmedBy = $confirmedBy;

        return $this;
    }

    public function getCollectedBy(): ?User
    {
        return $this->collectedBy;
    }

    public function setCollectedBy(?User $collectedBy): static
    {
        $this->collectedBy = $collectedBy;

        return $this;
    }

    /**
     * @return Collection<int, CollecteDetaillesPea>
     */
    public function getCollecteDetaillesPeas(): Collection
    {
        return $this->collecteDetaillesPeas;
    }

    public function addCollecteDetaillesPea(CollecteDetaillesPea $collecteDetaillesPea): static
    {
        if (!$this->collecteDetaillesPeas->contains($collecteDetaillesPea)) {
            $this->collecteDetaillesPeas->add($collecteDetaillesPea);
            $collecteDetaillesPea->setCollecte($this);
        }

        return $this;
    }

    public function removeCollecteDetaillesPea(CollecteDetaillesPea $collecteDetaillesPea): static
    {
        if ($this->collecteDetaillesPeas->removeElement($collecteDetaillesPea)) {
            // set the owning side to null (unless already changed)
            if ($collecteDetaillesPea->getCollecte() === $this) {
                $collecteDetaillesPea->setCollecte(null);
            }
        }

        return $this;
    }

    public function getCollecteType(): ?string
    {
        return $this->collecteType;
    }

    public function setCollecteType(string $collecteType): static
    {
        $this->collecteType = $collecteType;

        return $this;
    }

    public function getReference(): ?string
    {
        return $this->reference;
    }

    public function setReference(string $reference): static
    {
        $this->reference = $reference;

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

    public function getTotale(): ?float
    {
        return $this->totale;
    }

    public function setTotale(?float $totale): static
    {
        $this->totale = $totale;

        return $this;
    }
}
