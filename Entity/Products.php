<?php

namespace App\Entity;

use App\Entity\Trait\CreatedAtTrait;
use App\Entity\Trait\SlugTrait;
use App\Repository\ProductsRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ProductsRepository::class)]
class Products
{
    use CreatedAtTrait;
    use SlugTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\NotBlank(message: 'Le nom du produit ne peut pas être vide')]
    #[Assert\Length(
        min: 4,
        max: 200,
        minMessage: 'Le titre doit faire au moins {{ limit }} caractères',
        maxMessage: 'Le titre ne doit pas faire plus de {{ limit }} caractères'
    )]
    private $name;

    #[ORM\Column(type: 'text')]
    private $description;


    #[ORM\Column(type: 'integer')]
    #[Assert\PositiveOrZero(message: 'Le stock ne peut pas être négatif')]
    private $stock;

    #[ORM\ManyToOne(targetEntity: Categories::class, inversedBy: 'products')]
    #[ORM\JoinColumn(nullable: false)]
    private $categories;

    #[ORM\OneToMany(mappedBy: 'products', targetEntity: Images::class, orphanRemoval: true, cascade: ['persist'])]
    private $images;

    #[ORM\ManyToMany(targetEntity: OrderQuantity::class, mappedBy: 'product')]
    private Collection $orderQuantities;

    #[ORM\Column(nullable: true)]
    #[Assert\PositiveOrZero(message: 'Le stock ne peut pas être négatif')]
    private ?int $stockmin = null;

    #[ORM\Column]
    private ?int $maxq = null;



    public function __construct()
    {
        $this->images = new ArrayCollection();
        $this->created_at = new \DateTimeImmutable();
        $this->orderQuantities = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }


    public function getStock(): ?int
    {
        return $this->stock;
    }

    public function setStock(int $stock): self
    {
        $this->stock = $stock;

        return $this;
    }

    public function getCategories(): ?Categories
    {
        return $this->categories;
    }

    public function setCategories(?Categories $categories): self
    {
        $this->categories = $categories;

        return $this;
    }

    /**
     * @return Collection|Images[]
     */
    public function getImages(): Collection
    {
        return $this->images;
    }

    public function addImage(Images $image): self
    {
        if (!$this->images->contains($image)) {
            $this->images[] = $image;
            $image->setProducts($this);
        }

        return $this;
    }

    public function removeImage(Images $image): self
    {
        if ($this->images->removeElement($image)) {
            // set the owning side to null (unless already changed)
            if ($image->getProducts() === $this) {
                $image->setProducts(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, OrderQuantity>
     */
    public function getOrderQuantities(): Collection
    {
        return $this->orderQuantities;
    }

    public function addOrderQuantity(OrderQuantity $orderQuantity): self
    {
        if (!$this->orderQuantities->contains($orderQuantity)) {
            $this->orderQuantities->add($orderQuantity);
            $orderQuantity->addProduct($this);
        }

        return $this;
    }

    public function removeOrderQuantity(OrderQuantity $orderQuantity): self
    {
        if ($this->orderQuantities->removeElement($orderQuantity)) {
            $orderQuantity->removeProduct($this);
        }

        return $this;
    }

    public function getStockmin(): ?int
    {
        return $this->stockmin;
    }

    public function setStockmin(?int $stockmin): self
    {
        $this->stockmin = $stockmin;

        return $this;
    }

    public function getMaxq(): ?int
    {
        return $this->maxq;
    }

    public function setMaxq(int $maxq): self
    {
        $this->maxq = $maxq;

        return $this;
    }



   
}
