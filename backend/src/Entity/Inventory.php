<?php

namespace App\Entity;

use App\Repository\InventoryRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: InventoryRepository::class)]
class Inventory
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'inventories')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Item $item;

    #[ORM\ManyToOne(inversedBy: 'inventories')]
    #[ORM\JoinColumn(name: 'player_id', referencedColumnName: 'steam_id', nullable: false)]
    private User $player;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getItem(): ?item
    {
        return $this->item;
    }

    public function setItem(?item $item): static
    {
        $this->item = $item;

        return $this;
    }

    public function getPlayer(): ?user
    {
        return $this->player;
    }

    public function setPlayer(?user $player): static
    {
        $this->player = $player;

        return $this;
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
}
