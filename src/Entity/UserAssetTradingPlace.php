<?php

namespace App\Entity;

use App\Repository\UserAssetTradingPlaceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserAssetTradingPlaceRepository::class)]
#[ORM\UniqueConstraint(name: 'unique_user_asset_tradingplace', columns: ['tradingplace_user_id', 'asset_id', 'trading_place_id'])]
class UserAssetTradingPlace
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;


    #[ORM\ManyToOne(inversedBy: 'userAssetTradingPlaces')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $tradingplace_user = null;

    #[ORM\ManyToOne(inversedBy: 'userAssetTradingPlaces')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Asset $asset = null;

    #[ORM\ManyToOne(inversedBy: 'userAssetTradingPlaces')]
    #[ORM\JoinColumn(nullable: false)]
    private ?TradingPlace $tradingPlace = null;

    /**
     * @var Collection<int, DailyPerformance>
     */
    #[ORM\OneToMany(targetEntity: DailyPerformance::class, mappedBy: 'userAssetTradingPlace', orphanRemoval: true)]
    private Collection $dailyPerformances;

    public function __construct()
    {
        $this->dailyPerformances = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTradingplaceUser(): ?User
    {
        return $this->tradingplace_user;
    }

    public function setTradingplaceUser(?User $tradingplace_user): static
    {
        $this->tradingplace_user = $tradingplace_user;

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

    public function getTradingPlace(): ?TradingPlace
    {
        return $this->tradingPlace;
    }

    public function setTradingPlace(?TradingPlace $tradingPlace): static
    {
        $this->tradingPlace = $tradingPlace;

        return $this;
    }

    /**
     * @return Collection<int, DailyPerformance>
     */
    public function getDailyPerformances(): Collection
    {
        return $this->dailyPerformances;
    }

    public function addDailyPerformance(DailyPerformance $dailyPerformance): static
    {
        if (!$this->dailyPerformances->contains($dailyPerformance)) {
            $this->dailyPerformances->add($dailyPerformance);
            $dailyPerformance->setUserAssetTradingPlace($this);
        }

        return $this;
    }

    public function removeDailyPerformance(DailyPerformance $dailyPerformance): static
    {
        if ($this->dailyPerformances->removeElement($dailyPerformance)) {
            // set the owning side to null (unless already changed)
            if ($dailyPerformance->getUserAssetTradingPlace() === $this) {
                $dailyPerformance->setUserAssetTradingPlace(null);
            }
        }

        return $this;
    }

}
