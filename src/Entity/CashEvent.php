<?php

namespace App\Entity;

use App\Repository\CashEventRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CashEventRepository::class)]
class CashEvent
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'cashEvents')]
    #[ORM\JoinColumn(nullable: false)]
    private ?UserTradingPlace $userTradingPlace = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    private ?\DateTimeImmutable $date = null;


    #[ORM\Column(type: Types::DECIMAL, precision: 14, scale: 4)]
    private ?string $amount = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $description = null;

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

    public function getAmount(): ?string
    {
        return $this->amount;
    }

    public function setAmount(string $amount): static
    {
        $this->amount = $amount;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }
}
