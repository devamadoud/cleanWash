<?php

namespace App\Entity;

use App\Repository\ProductRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProductRepository::class)]
class Product
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(nullable: true)]
    private ?float $purchasePrice = null;

    #[ORM\Column]
    private ?float $price = null;

    #[ORM\Column(nullable: true)]
    private ?int $promo = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column(nullable: true)]
    private ?bool $isPublished = null;

    #[ORM\ManyToOne(inversedBy: 'products')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Shop $shop = null;

    #[ORM\Column(length: 255)]
    private ?string $productImage = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $productDescription = null;

    #[ORM\Column(nullable: true)]
    private ?int $quantityStocke = null;

    /**
     * @var Collection<int, OrderDetailles>
     */
    #[ORM\OneToMany(targetEntity: OrderDetailles::class, mappedBy: 'product', orphanRemoval: true)]
    private Collection $orderDetailles;

    /**
     * @var Collection<int, Categories>
     */
    #[ORM\ManyToMany(targetEntity: Categories::class, inversedBy: 'products')]
    private Collection $category;

    #[ORM\Column(nullable: true)]
    private ?float $promoPrice = null;

    #[ORM\Column(nullable: true)]
    private ?int $quantitySales = null;

    public function __construct()
    {
        $this->orderDetailles = new ArrayCollection();
        $this->category = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getPurchasePrice(): ?float
    {
        return $this->purchasePrice;
    }

    public function setPurchasePrice(?float $purchasePrice): static
    {
        $this->purchasePrice = $purchasePrice;

        return $this;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(float $price): static
    {
        $this->price = $price;

        return $this;
    }

    public function getPromo(): ?int
    {
        return $this->promo;
    }

    public function setPromo(?int $promo): static
    {
        $this->promo = $promo;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt ?? new \DateTimeImmutable();
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = new \DateTimeImmutable();

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt ?? new \DateTimeImmutable();
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt =  new \DateTimeImmutable();

        return $this;
    }

    public function isPublished(): ?bool
    {
        return $this->isPublished;
    }

    public function setPublished(?bool $isPublished): static
    {
        $this->isPublished = $isPublished;

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

    public function getProductImage(): ?string
    {
        return $this->productImage;
    }

    public function setProductImage(string $productImage): static
    {
        $this->productImage = $productImage;

        return $this;
    }

    public function getProductDescription(): ?string
    {
        return $this->productDescription;
    }

    public function setProductDescription(?string $productDescription): static
    {
        $this->productDescription = $productDescription;

        return $this;
    }

    public function getQuantityStocke(): ?int
    {
        return $this->quantityStocke;
    }

    public function setQuantityStocke(?int $quantityStocke): static
    {
        $this->quantityStocke = $quantityStocke;

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
            $orderDetaille->setProduct($this);
        }

        return $this;
    }

    public function removeOrderDetaille(OrderDetailles $orderDetaille): static
    {
        if ($this->orderDetailles->removeElement($orderDetaille)) {
            // set the owning side to null (unless already changed)
            if ($orderDetaille->getProduct() === $this) {
                $orderDetaille->setProduct(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Categories>
     */
    public function getCategory(): Collection
    {
        return $this->category;
    }

    public function addCategory(Categories $category): static
    {
        if (!$this->category->contains($category)) {
            $this->category->add($category);
        }

        return $this;
    }

    public function removeCategory(Categories $category): static
    {
        $this->category->removeElement($category);

        return $this;
    }

    public function getPromoPrice(): ?float
    {
        return $this->promoPrice;
    }

    public function setPromoPrice(?float $promoPrice): static
    {
        $this->promoPrice = $promoPrice;

        return $this;
    }

    public function getQuantitySales(): ?int
    {
        return $this->quantitySales;
    }

    public function setQuantitySales(?int $quantitySales): static
    {
        $this->quantitySales = $quantitySales;

        return $this;
    }
}
