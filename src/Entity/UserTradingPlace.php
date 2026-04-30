<?php

namespace App\Entity;

use App\Repository\UserTradingPlaceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserTradingPlaceRepository::class)]
#[ORM\UniqueConstraint(name: 'unique_user_trading_place', columns: ['tradingplace_user_id', 'trading_place_id'])]
class UserTradingPlace
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'userTradingPlaces')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $tradingplaceUser = null;

    #[ORM\ManyToOne(inversedBy: 'userTradingPlaces')]
    #[ORM\JoinColumn(nullable: false)]
    private ?TradingPlace $tradingPlace = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 14, scale: 4)]
    private ?string $startingCash = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 5, scale: 4)]
    private ?string $taxRate = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 14, scale: 4)]
    private ?string $taxFreePot = null;

    /**
     * @var Collection<int, TradingPlacePerformance>
     */
    #[ORM\OneToMany(targetEntity: TradingPlacePerformance::class, mappedBy: 'userTradingPlace', orphanRemoval: true)]
    private Collection $tradingPlacePerformances;

    /**
     * @var Collection<int, CashEvent>
     */
    #[ORM\OneToMany(targetEntity: CashEvent::class, mappedBy: 'userTradingPlace', orphanRemoval: true)]
    private Collection $cashEvents;

    public function __construct()
    {
        $this->tradingPlacePerformances = new ArrayCollection();
        $this->cashEvents = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTradingplaceUser(): ?User
    {
        return $this->tradingplaceUser;
    }

    public function setTradingplaceUser(?User $tradingplaceUser): static
    {
        $this->tradingplaceUser = $tradingplaceUser;

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

    public function getStartingCash(): ?string
    {
        return $this->startingCash;
    }

    public function setStartingCash(string $startingCash): static
    {
        $this->startingCash = $startingCash;

        return $this;
    }

    public function getTaxRate(): ?string
    {
        return $this->taxRate;
    }

    public function setTaxRate(string $taxRate): static
    {
        $this->taxRate = $taxRate;

        return $this;
    }

    public function getTaxFreePot(): ?string
    {
        return $this->taxFreePot;
    }

    public function setTaxFreePot(string $taxFreePot): static
    {
        $this->taxFreePot = $taxFreePot;

        return $this;
    }

    /**
     * @return Collection<int, TradingPlacePerformance>
     */
    public function getTradingPlacePerformances(): Collection
    {
        return $this->tradingPlacePerformances;
    }

    public function addTradingPlacePerformance(TradingPlacePerformance $tradingPlacePerformance): static
    {
        if (!$this->tradingPlacePerformances->contains($tradingPlacePerformance)) {
            $this->tradingPlacePerformances->add($tradingPlacePerformance);
            $tradingPlacePerformance->setUserTradingPlace($this);
        }

        return $this;
    }

    public function removeTradingPlacePerformance(TradingPlacePerformance $tradingPlacePerformance): static
    {
        if ($this->tradingPlacePerformances->removeElement($tradingPlacePerformance)) {
            // set the owning side to null (unless already changed)
            if ($tradingPlacePerformance->getUserTradingPlace() === $this) {
                $tradingPlacePerformance->setUserTradingPlace(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, CashEvent>
     */
    public function getCashEvents(): Collection
    {
        return $this->cashEvents;
    }

    public function addCashEvent(CashEvent $cashEvents): static
    {
        if (!$this->cashEvents->contains($cashEvents)) {
            $this->cashEvents->add($cashEvents);
            $cashEvents->setUserTradingPlace($this);
        }

        return $this;
    }

    public function removeCashEvents(CashEvent $cashEvents): static
    {
        if ($this->cashEvents->removeElement($cashEvents)) {
            // set the owning side to null (unless already changed)
            if ($cashEvents->getUserTradingPlace() === $this) {
                $cashEvents->setUserTradingPlace(null);
            }
        }

        return $this;
    }
}
