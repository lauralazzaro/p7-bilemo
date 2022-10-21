<?php

namespace App\Entity;

use App\Repository\ProductRepository;
use JMS\Serializer\Annotation\Groups;
use Doctrine\ORM\Mapping as ORM;

use Hateoas\Configuration\Annotation as Hateoas;

/**
 * @Hateoas\Relation(
 *      "self",
 *      href = @Hateoas\Route(
 *          "app_product_detail",
 *          parameters = { "id" = "expr(object.getId())" }
 *      ),
 *     exclusion = @Hateoas\Exclusion(groups="getProducts")
 * )
 *
 */
#[ORM\Entity(repositoryClass: ProductRepository::class)]
class Product
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["getProducts"])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(["getProducts"])]
    private ?string $model = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(["getProducts"])]
    private ?string $memory = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(["getProducts"])]
    private ?string $color = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(["getProducts"])]
    private ?string $rearCamera = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(["getProducts"])]
    private ?string $screenSize = null;

    #[ORM\ManyToOne(inversedBy: 'products')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(["getProducts"])]
    private ?Brand $brand = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getModel(): ?string
    {
        return $this->model;
    }

    public function setModel(string $model): self
    {
        $this->model = $model;

        return $this;
    }

    public function getBrand(): ?Brand
    {
        return $this->brand;
    }

    public function setBrand(?Brand $brand): self
    {
        $this->brand = $brand;

        return $this;
    }

    public function getMemory(): ?string
    {
        return $this->memory;
    }

    public function setMemory(?string $memory): self
    {
        $this->memory = $memory;

        return $this;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(?string $color): self
    {
        $this->color = $color;

        return $this;
    }

    public function getRearCamera(): ?string
    {
        return $this->rearCamera;
    }

    public function setRearCamera(?string $rearCamera): self
    {
        $this->rearCamera = $rearCamera;

        return $this;
    }

    public function getScreenSize(): ?string
    {
        return $this->screenSize;
    }

    public function setScreenSize(?string $screenSize): self
    {
        $this->screenSize = $screenSize;

        return $this;
    }
}
