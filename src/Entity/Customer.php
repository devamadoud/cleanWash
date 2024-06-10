<?php

namespace App\Entity;

use App\Repository\CustomerRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CustomerRepository::class)]
class Customer
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $fullName = null;

    #[ORM\Column(length: 255)]
    private ?string $phoneNumber = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $adress = null;

    #[ORM\ManyToOne(inversedBy: 'customers')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Shop $shop = null;

    /**
     * @var Collection<int, Collecte>
     */
    #[ORM\OneToMany(targetEntity: Collecte::class, mappedBy: 'customer', orphanRemoval: true, cascade: ['persist'])]
    private Collection $collectes;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $coordLng = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $coordLat = null;

    /**
     * @var Collection<int, Order>
     */
    #[ORM\OneToMany(targetEntity: Order::class, mappedBy: 'customer', orphanRemoval: true)]
    private Collection $orders;

    public function __construct()
    {
        $this->collectes = new ArrayCollection();
        $this->orders = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFullName(): ?string
    {
        return $this->fullName;
    }

    public function setFullName(string $fullName): static
    {
        $this->fullName = $fullName;

        return $this;
    }

    public function getPhoneNumber(): ?string
    {
        return $this->phoneNumber;
    }

    public function setPhoneNumber(string $phoneNumber): static
    {
        $this->phoneNumber = $phoneNumber;

        return $this;
    }

    public function getAdress(): ?string
    {
        return $this->adress;
    }

    public function setAdress(string $adress): static
    {
        $this->adress = $adress;

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

    /**
     * @return Collection<int, Collecte>
     */
    public function getCollectes(): Collection
    {
        return $this->collectes;
    }

    public function addCollecte(Collecte $collecte): static
    {
        if (!$this->collectes->contains($collecte)) {
            $this->collectes->add($collecte);
            $collecte->setCustomer($this);
        }

        return $this;
    }

    public function removeCollecte(Collecte $collecte): static
    {
        if ($this->collectes->removeElement($collecte)) {
            // set the owning side to null (unless already changed)
            if ($collecte->getCustomer() === $this) {
                $collecte->setCustomer(null);
            }
        }

        return $this;
    }

    public function getCoordLng(): ?string
    {
        return $this->coordLng;
    }

    public function setCoordLng(?string $coordLng): static
    {
        $this->coordLng = $coordLng;

        return $this;
    }

    public function getCoordLat(): ?string
    {
        return $this->coordLat;
    }

    public function setCoordLat(?string $coordLat): static
    {
        $this->coordLat = $coordLat;

        return $this;
    }

    /**
     * @return Collection<int, Order>
     */
    public function getOrders(): Collection
    {
        return $this->orders;
    }

    public function addOrder(Order $order): static
    {
        if (!$this->orders->contains($order)) {
            $this->orders->add($order);
            $order->setCustomer($this);
        }

        return $this;
    }

    public function removeOrder(Order $order): static
    {
        if ($this->orders->removeElement($order)) {
            // set the owning side to null (unless already changed)
            if ($order->getCustomer() === $this) {
                $order->setCustomer(null);
            }
        }

        return $this;
    }
}
