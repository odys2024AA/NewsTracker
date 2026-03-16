<?php

namespace App\Entity;

use App\Repository\WatchlistAssetRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity(repositoryClass: WatchlistAssetRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_SYMBOL', fields: ['symbol'])]
class WatchlistAsset
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 10)]
    private ?string $symbol = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(type: 'decimal', precision: 20, scale: 10)]
    private ?string $quote = null;

    #[ORM\Column(nullable: true)]
    private ?float $change = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    /**
     * @var Collection<int, User>
     */
    #[ORM\ManyToMany(targetEntity: User::class, inversedBy: 'watchlistAssets')]
    private Collection $appUsers;

    public function __construct() {
        $this->createdAt = new \DateTimeImmutable();
        $this->appUsers = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSymbol(): ?string
    {
        return $this->symbol;
    }

    public function setSymbol(string $symbol): static
    {
        $this->symbol = $symbol;

        return $this;
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

    public function getQuote(): ?string
    {
        return $this->quote;
    }

    public function setQuote(?string $quote): static
    {
        $this->quote = $quote;

        return $this;
    }

    public function getChange(): ?float
    {
        return $this->change;
    }

    public function setChange(?float $change): static
    {
        $this->change = $change;

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getAppUsers(): Collection
    {
        return $this->appUsers;
    }

    public function addAppUser(User $appUser): static
    {
        if (!$this->appUsers->contains($appUser)) {
            $this->appUsers->add($appUser);
            $appUser->addWatchlistAsset($this);
        }
        return $this;
    }

    public function removeAppUser(User $appUser): static
    {
        $this->appUsers->removeElement($appUser);
        return $this;
    }
}
