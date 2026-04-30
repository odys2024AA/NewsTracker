<?php

namespace App\Entity;

use App\Repository\DailyPerformanceRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DailyPerformanceRepository::class)]
#[ORM\UniqueConstraint(name: 'unique_uastp_date', columns:['user_asset_trading_place_id', 'date'])]
class DailyPerformance
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'dailyPerformances')]
    #[ORM\JoinColumn(nullable: false)]
    private ?UserAssetTradingPlace $userAssetTradingPlace = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 14, scale: 4, nullable: true)]
    private ?string $fee = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 14, scale: 4, nullable: true)]
    private ?string $tax = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 14, scale: 4, nullable: true)]
    private ?string $realized_earnings = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 14, scale: 4, nullable: true)]
    private ?string $realized_earnings_after_fees = null;


    #[ORM\Column(type: Types::DECIMAL, precision: 14, scale: 4, nullable: true)]
    private ?string $realized_earnings_current_year = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 14, scale: 4, nullable: true)]
    private ?string $leftover = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 14, scale: 4, nullable: true)]
    private ?string $bought_value = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 14, scale: 4, nullable: true)]
    private ?string $sold_value = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 14, scale: 4, nullable: true)]
    private ?string $currently_invested = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 14, scale: 4, nullable: true)]
    private ?string $current_value = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 14, scale: 4, nullable: true)]
    private ?string $potential_earnings = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 14, scale: 4, nullable: true)]
    private ?string $overall_earnings = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    private ?\DateTimeImmutable $date = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUserAssetTradingPlace(): ?UserAssetTradingPlace
    {
        return $this->userAssetTradingPlace;
    }

    public function setUserAssetTradingPlace(?UserAssetTradingPlace $userAssetTradingPlace): static
    {
        $this->userAssetTradingPlace = $userAssetTradingPlace;

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

    public function getTax(): ?string
    {
        return $this->tax;
    }

    public function setTax(?string $tax): static
    {
        $this->tax = $tax;

        return $this;
    }

    public function getRealizedEarnings(): ?string
    {
        return $this->realized_earnings;
    }

    public function setRealizedEarnings(?string $realized_earnings): static
    {
        $this->realized_earnings = $realized_earnings;

        return $this;
    }

    public function getRealizedEarningsCurrentYear(): ?string
    {
        return $this->realized_earnings_current_year;
    }

    public function getRealizedEarningsAfterFees(): ?string
    {
        return $this->realized_earnings_after_fees;
    }

    public function setRealizedEarningsAfterFees(?string $realized_earnings_after_fees): static
    {
        $this->realized_earnings_after_fees = $realized_earnings_after_fees;
        return $this;
    }

    public function setRealizedEarningsCurrentYear(?string $realized_earnings_current_year): static
    {
        $this->realized_earnings_current_year = $realized_earnings_current_year;

        return $this;
    }


    public function getLeftover(): ?string
    {
        return $this->leftover;
    }

    public function setLeftover(?string $leftover): static
    {
        $this->leftover = $leftover;

        return $this;
    }

    public function getBoughtValue(): ?string
    {
        return $this->bought_value;
    }

    public function setBoughtValue(?string $bought_value): static
    {
        $this->bought_value = $bought_value;

        return $this;
    }

    public function getSoldValue(): ?string
    {
        return $this->sold_value;
    }

    public function setSoldValue(?string $sold_value): static
    {
        $this->sold_value = $sold_value;

        return $this;
    }

    public function getCurrentlyInvested(): ?string
    {
        return $this->currently_invested;
    }

    public function setCurrentlyInvested(?string $currently_invested): static
    {
        $this->currently_invested = $currently_invested;

        return $this;
    }

    public function getCurrentValue(): ?string
    {
        return $this->current_value;
    }

    public function setCurrentValue(?string $current_value): static
    {
        $this->current_value = $current_value;

        return $this;
    }

    public function getPotentialEarnings(): ?string
    {
        return $this->potential_earnings;
    }

    public function setPotentialEarnings(?string $potential_earnings): static
    {
        $this->potential_earnings = $potential_earnings;

        return $this;
    }

    public function getOverallEarnings(): ?string
    {
        return $this->overall_earnings;
    }

    public function setOverallEarnings(?string $overall_earnings): static
    {
        $this->overall_earnings = $overall_earnings;

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
}
