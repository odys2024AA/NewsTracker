<?php

namespace App\Entity;

use App\Repository\TradingPlaceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TradingPlaceRepository::class)]
class TradingPlace
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $name = null;

    /**
     * @var Collection<int, Transaction>
     */
    #[ORM\OneToMany(targetEntity: Transaction::class, mappedBy: 'tradingPlace')]
    private Collection $trading_place_transactions;

    /**
     * @var Collection<int, UserAssetTradingPlace>
     */
    #[ORM\OneToMany(targetEntity: UserAssetTradingPlace::class, mappedBy: 'tradingPlace')]
    private Collection $userAssetTradingPlaces;

    /**
     * @var Collection<int, UserTradingPlace>
     */
    #[ORM\OneToMany(targetEntity: UserTradingPlace::class, mappedBy: 'tradingPlace')]
    private Collection $userTradingPlaces;

    public function __construct()
    {
        $this->trading_place_transactions = new ArrayCollection();
        $this->userAssetTradingPlaces = new ArrayCollection();
        $this->userTradingPlaces = new ArrayCollection();
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

    public function __toString(): string
    {
        return $this ->name;
    }

    /**
     * @return Collection<int, Transaction>
     */
    public function getTradingPlaceTransactions(): Collection
    {
        return $this->trading_place_transactions;
    }

    public function addTradingPlaceTransaction(Transaction $tradingPlaceTransaction): static
    {
        if (!$this->trading_place_transactions->contains($tradingPlaceTransaction)) {
            $this->trading_place_transactions->add($tradingPlaceTransaction);
            $tradingPlaceTransaction->setTradingPlace($this);
        }

        return $this;
    }

    public function removeTradingPlaceTransaction(Transaction $tradingPlaceTransaction): static
    {
        if ($this->trading_place_transactions->removeElement($tradingPlaceTransaction)) {
            // set the owning side to null (unless already changed)
            if ($tradingPlaceTransaction->getTradingPlace() === $this) {
                $tradingPlaceTransaction->setTradingPlace(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, UserAssetTradingPlace>
     */
    public function getUserAssetTradingPlaces(): Collection
    {
        return $this->userAssetTradingPlaces;
    }

    public function addUserAssetTradingPlace(UserAssetTradingPlace $userAssetTradingPlace): static
    {
        if (!$this->userAssetTradingPlaces->contains($userAssetTradingPlace)) {
            $this->userAssetTradingPlaces->add($userAssetTradingPlace);
            $userAssetTradingPlace->setTradingPlace($this);
        }

        return $this;
    }

    public function removeUserAssetTradingPlace(UserAssetTradingPlace $userAssetTradingPlace): static
    {
        if ($this->userAssetTradingPlaces->removeElement($userAssetTradingPlace)) {
            // set the owning side to null (unless already changed)
            if ($userAssetTradingPlace->getTradingPlace() === $this) {
                $userAssetTradingPlace->setTradingPlace(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, UserTradingPlace>
     */
    public function getUserTradingPlaces(): Collection
    {
        return $this->userTradingPlaces;
    }

    public function addUserTradingPlace(UserTradingPlace $userTradingPlace): static
    {
        if (!$this->userTradingPlaces->contains($userTradingPlace)) {
            $this->userTradingPlaces->add($userTradingPlace);
            $userTradingPlace->setTradingPlace($this);
        }

        return $this;
    }

    public function removeUserTradingPlace(UserTradingPlace $userTradingPlace): static
    {
        if ($this->userTradingPlaces->removeElement($userTradingPlace)) {
            // set the owning side to null (unless already changed)
            if ($userTradingPlace->getTradingPlace() === $this) {
                $userTradingPlace->setTradingPlace(null);
            }
        }

        return $this;
    }
}
