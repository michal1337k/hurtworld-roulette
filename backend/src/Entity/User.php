<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
class User implements UserInterface
{
    #[ORM\Id]
    #[ORM\Column(name: 'steam_id', length: 32)]
    private string $steamId;

    #[ORM\Column(length: 255)]
    private ?string $nickname = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $avatar = null;

    #[ORM\Column(nullable: true)]
    private ?int $balance = null;

    #[ORM\Column]
    private array $roles = [];

    /**
     * @var Collection<int, Inventory>
     */
    #[ORM\OneToMany(targetEntity: Inventory::class, mappedBy: 'player')]
    private Collection $inventories;

    public function __construct()
    {
        $this->inventories = new ArrayCollection();
    }

    public function setSteamId(string $steamId): static
    {
        $this->steamId = $steamId;
        return $this;
    }

    public function getSteamId(): string
    {
        return $this->steamId;
    }

    public function getNickname(): ?string
    {
        return $this->nickname;
    }

    public function setNickname(string $nickname): static
    {
        $this->nickname = $nickname;

        return $this;
    }

    public function getAvatar(): ?string
    {
        return $this->avatar;
    }

    public function setAvatar(?string $avatar): static
    {
        $this->avatar = $avatar;

        return $this;
    }

    public function getBalance(): ?int
    {
        return $this->balance;
    }

    public function setBalance(?int $balance): static
    {
        $this->balance = $balance;

        return $this;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @return Collection<int, Inventory>
     */
    public function getInventories(): Collection
    {
        return $this->inventories;
    }

    public function addInventory(Inventory $inventory): static
    {
        if (!$this->inventories->contains($inventory)) {
            $this->inventories->add($inventory);
            $inventory->setPlayer($this);
        }

        return $this;
    }

    public function removeInventory(Inventory $inventory): static
    {
        if ($this->inventories->removeElement($inventory)) {
            // set the owning side to null (unless already changed)
            if ($inventory->getPlayer() === $this) {
                $inventory->setPlayer(null);
            }
        }

        return $this;
    }

    public function getUserIdentifier(): string
    {
        return (string) $this->steamId;
    }

    public function eraseCredentials(): void
    {
        // no-op
    }
}
