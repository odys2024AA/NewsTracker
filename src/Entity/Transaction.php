<?php

namespace App\Entity;

use App\Repository\TransactionRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: TransactionRepository::class)]
class Transaction
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'transactions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Asset $asset = null;

    #[ORM\ManyToOne(inversedBy:'transactions')]
    #[ORM\JoinColumn(nullable:false)]
    private ?User $user = null;


    #[ORM\Column(length: 10)]
    #[Assert\Choice(['BUY', 'SELL'])]
    private ?string $transaction_type = null;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $date = null;

    #[ORM\Column(type: 'decimal', precision: 12, scale: 4)]
    private ?string $price = null;

    #[ORM\Column(type: 'decimal', precision: 12, scale: 4)]
    private ?string $quantity = null;

    #[ORM\Column(type: 'decimal', precision: 12, scale: 4, nullable: true)]
    private ?string $fee = null;

    #[ORM\ManyToOne(inversedBy: 'trading_place_transactions')]
    private ?TradingPlace $tradingPlace = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setuser(?User $user): self 
    {
        $this->user = $user;
        return $this;
    }

    public function getAsset(): ?Asset
    {
        return $this->asset;
    }

    public function setAsset(?Asset $asset): static
    {
        $this->asset = $asset;

        return $this;
    }

    public function getTransactionType(): ?string
    {
        return $this->transaction_type;
    }

    public function setTransactionType(string $transaction_type): static
    {
        $this->transaction_type = $transaction_type;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function getPrice(): ?string
    {
        return $this->price;
    }

    public function setPrice(string $price): static
    {
        $this->price = $price;

        return $this;
    }

    public function getQuantity(): ?string
    {
        return $this->quantity;
    }

    public function setQuantity(string $quantity): static
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function getFee(): ?string
    {
        return $this->fee;
    }

    public function setFee(?string $fee): static
    {
        $this->fee = $fee;

        return $this;
    }

    public function getTradingPlace(): ?TradingPlace
    {
        return $this->tradingPlace;
    }

    public function setTradingPlace(?TradingPlace $tradingPlace): static
    {
        $this->tradingPlace = $tradingPlace;
        return $this;
    }
}
