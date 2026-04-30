<?php

namespace App\Entity;

use App\Repository\TradingPlacePerformanceRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TradingPlacePerformanceRepository::class)]
#[ORM\UniqueConstraint(name: 'unique_utp_date', columns: ['user_trading_place_id', 'date' ])]
class TradingPlacePerformance
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'tradingPlacePerformances')]
    #[ORM\JoinColumn(nullable: false)]
    private ?UserTradingPlace $userTradingPlace = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    private ?\DateTimeImmutable $date = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 14, scale: 4, nullable: true)]
    private ?string $cash = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 14, scale: 4, nullable: true)]
    private ?string $tax = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 14, scale: 4, nullable: true)]
    private ?string $paidTaxCurrentYear = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 14, scale: 4, nullable: true)]
    private ?string $taxableEarnings = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 14, scale: 4, nullable: true)]
    private ?string $realizedEarningsCurrentYear = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 14, scale: 4, nullable: true)]
    private ?string $realizedEarnings = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 14, scale: 4, nullable: true)]
    private ?string $overallEarnings = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUserTradingPlace(): ?UserTradingPlace
    {
        return $this->userTradingPlace;
    }

    public function setUserTradingPlace(?UserTradingPlace $userTradingPlace): static
    {
        $this->userTradingPlace = $userTradingPlace;

        return $this;
    }

    public function getDate(): ?\DateTimeImmutable
    {
        return $this->date;
    }

    public function setDate(\DateTimeImmutable $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function getCash(): ?string
    {
        return $this->cash;
    }

    public function setCash(?string $cash): static
    {
        $this->cash = $cash;

        return $this;
    }

    public function getTax(): ?string
    {
        return $this->tax;
    }

    public function setTax(?string $tax): static
    {
        $this->tax = $tax;

        return $this;
    }

    public function getPaidTaxCurrentYear(): ?string
    {
        return $this->paidTaxCurrentYear;
    }

    public function setPaidTaxCurrentYear(?string $paidTaxCurrentYear): static
    {
        $this->paidTaxCurrentYear = $paidTaxCurrentYear;

        return $this;
    }

    public function getTaxableEarnings(): ?string
    {
        return $this->taxableEarnings;
    }

    public function setTaxableEarnings(?string $taxableEarnings): static
    {
        $this->taxableEarnings = $taxableEarnings;

        return $this;
    }

    public function getRealizedEarningsCurrentYear(): ?string
    {
        return $this->realizedEarningsCurrentYear;
    }

    public function setRealizedEarningsCurrentYear(?string $realizedEarningsCurrentYear): static
    {
        $this->realizedEarningsCurrentYear = $realizedEarningsCurrentYear;

        return $this;
    }

    public function getRealizedEarnings(): ?string
    {
        return $this->realizedEarnings;
    }

    public function setRealizedEarnings(?string $realizedEarnings): static
    {
        $this->realizedEarnings = $realizedEarnings;

        return $this;
    }

    public function getOverallEarnings(): ?string
    {
        return $this->overallEarnings;
    }

    public function setOverallEarnings(?string $overallEarnings): static
    {
        $this->overallEarnings = $overallEarnings;

        return $this;
    }
}
