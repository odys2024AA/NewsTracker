<?php

namespace App\Entity;

use App\Repository\AssetRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: AssetRepository::class)]
class Asset
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 10)]
    private ?string $symbol = null;

    #[ORM\Column(length: 10)]
    #[Assert\Choice(['STOCK', 'CRYPTO', 'ETF', 'FUND'])]
    private ?string $asset_type = null;

    /**
     * @var Collection<int, AssetQuote>
     */
    #[ORM\OneToMany(targetEntity: AssetQuote::class, mappedBy: 'asset', orphanRemoval: true)]
    private Collection $quotes;

    /**
     * @var Collection<int, Transaction>
     */
    #[ORM\OneToMany(targetEntity: Transaction::class, mappedBy: 'asset', orphanRemoval: true)]
    private Collection $transactions;

    /**
     * @var Collection<int, UserAssetTradingPlace>
     */
    #[ORM\OneToMany(targetEntity: UserAssetTradingPlace::class, mappedBy: 'asset', orphanRemoval: true)]
    private Collection $userAssetTradingPlaces;

    public function __construct()
    {
        $this->quotes = new ArrayCollection();
        $this->transactions = new ArrayCollection();
        $this->userAssetTradingPlaces = new ArrayCollection();
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

    public function getAssetType(): ?string
    {
        return $this->asset_type;
    }

    public function setAssetType(string $asset_type): static
    {
        $this->asset_type = $asset_type;

        return $this;
    }

    /**
     * @return Collection<int, AssetQuote>
     */
    public function getQuotes(): Collection
    {
        return $this->quotes;
    }

    public function addQuote(AssetQuote $quote): static
    {
        if (!$this->quotes->contains($quote)) {
            $this->quotes->add($quote);
            $quote->setAsset($this);
        }

        return $this;
    }

    public function removeQuote(AssetQuote $quote): static
    {
        if ($this->quotes->removeElement($quote)) {
            // set the owning side to null (unless already changed)
            if ($quote->getAsset() === $this) {
                $quote->setAsset(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Transaction>
     */
    public function getTransactions(): Collection
    {
        return $this->transactions;
    }

    public function addTransaction(Transaction $transaction): static
    {
        if (!$this->transactions->contains($transaction)) {
            $this->transactions->add($transaction);
            $transaction->setAsset($this);
        }

        return $this;
    }

    public function removeTransaction(Transaction $transaction): static
    {
        if ($this->transactions->removeElement($transaction)) {
            // set the owning side to null (unless already changed)
            if ($transaction->getAsset() === $this) {
                $transaction->setAsset(null);
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
            $userAssetTradingPlace->setAsset($this);
        }

        return $this;
    }

    public function removeUserAssetTradingPlace(UserAssetTradingPlace $userAssetTradingPlace): static
    {
        if ($this->userAssetTradingPlaces->removeElement($userAssetTradingPlace)) {
            // set the owning side to null (unless already changed)
            if ($userAssetTradingPlace->getAsset() === $this) {
                $userAssetTradingPlace->setAsset(null);
            }
        }

        return $this;
    }
}
