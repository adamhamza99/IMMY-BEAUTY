<?php

namespace App\Entity;

use App\Repository\CardRepository;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints\Range;

#[ORM\Entity(repositoryClass: CardRepository::class)]
class Card
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(inversedBy: 'card', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\Column(length: 255)]
    #[Assert\File(mimeTypes: ["image/png", "image/jpeg"])]
    private ?string $qrCodeImage = null;

    #[ORM\Column(nullable: true)]
    #[Range(
        min: 0,
        max: 100,
        notInRangeMessage: "Points must be between {{ min }} and {{ max }}."
    )]
    private ?int $points = 0;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getQrCodeImage(): ?string
    {
        return $this->qrCodeImage;
    }

    public function setQrCodeImage(string $qrCodeImage): static
    {
        $this->qrCodeImage = $qrCodeImage;

        return $this;
    }

    public function getPoints(): ?int
    {
        return $this->points;
    }

    public function setPoints(?int $points): static
    {
        $this->points = $points;

        return $this;
    }
}
