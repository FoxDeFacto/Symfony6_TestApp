<?php

namespace App\Entity;

use App\Repository\ProductRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProductRepository::class)]
class Product
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private ?string $name = null;

    #[ORM\ManyToOne(targetEntity: ProductType::class)] //Každý produkt má jeden typ
    #[ORM\JoinColumn(name: "ProductType_id", referencedColumnName: "id")] //Název pod kterým se to vytvoří v databázi a sloupec pod který se to bude hledat v druhé tabulce
    private ?ProductType $productType = null;

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

    public function getProductType(): ?ProductType //Musí být pojmenováno ve stylu get/set a přesný název proměný/datového typu
    {
        return $this->productType;
    }

    public function setProductType(ProductType $productType): static
    {
        $this->productType = $productType;

        return $this;
    }

}
