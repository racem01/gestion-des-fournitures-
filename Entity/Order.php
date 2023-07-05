<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\OrderRepository;
use App\Entity\Trait\CreatedAtTrait;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

#[ORM\Entity(repositoryClass: OrderRepository::class)]
#[ORM\Table(name: '`order`')]
class Order
{
    use CreatedAtTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'orders')]
    private ?Users $user = null;

    #[ORM\ManyToMany(targetEntity: OrderQuantity::class, inversedBy: 'orders')]
    private Collection $products;

    #[ORM\Column(nullable: true)]
    private ?bool $isverified = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $dateliv = null;
    
    #[ORM\Column(type: 'datetime_immutable', options: ['default' => 'CURRENT_TIMESTAMP'])]
    private $created_at;

  

 




    public function __construct()
    {
        $this->products = new ArrayCollection();
        $this->created_at = new \DateTimeImmutable();
    }



    // #[ORM\OneToMany(targetEntity: OrderQuantity::class, mappedBy: 'order', cascade: ['persist'])]
    // private ?Collection $products = null;

    // public function __construct()
    // {
    //     $this->products = new ArrayCollection();
    // }




    public function getId(): ?int
    {
        return $this->id;
    }


    public function getUser(): ?Users
    {
        return $this->user;
    }

    public function setUser(?Users $user): self
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return Collection<int, OrderQuantity>
     */
    public function getProducts(): Collection
    {
        return $this->products;
    }

    public function addProduct(OrderQuantity $product): self
    {
        if (!$this->products->contains($product)) {
            $this->products->add($product);
        }

        return $this;
    }

    public function removeProduct(OrderQuantity $product): self
    {
        $this->products->removeElement($product);

        return $this;
    }

    public function isIsverified(): ?bool
    {
        return $this->isverified;
    }

    public function setIsverified(?bool $isverified): self
    {
        $this->isverified = $isverified;

        return $this;
    }

    public function getDateliv(): ?\DateTimeInterface
    {
        return $this->dateliv;
    }

    public function setDateliv(?\DateTimeInterface $dateliv): self
    {
        $this->dateliv = $dateliv;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeImmutable $created_at): self
    {
        $this->created_at = $created_at;

        return $this;
    }
}

   







