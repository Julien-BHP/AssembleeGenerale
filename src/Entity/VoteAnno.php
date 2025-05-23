<?php

namespace App\Entity;

use App\Repository\VoteAnnoRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: VoteAnnoRepository::class)]
class VoteAnno
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $parts = null;

    #[ORM\Column]
    private ?\DateTime $voteTime = null;

    #[ORM\Column(length: 255)]
    private ?string $decision = null;

    #[ORM\ManyToOne(inversedBy: 'voteAnnos')]
    #[ORM\JoinColumn(nullable: false)]
    private ?AGPoint $point = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getParts(): ?int
    {
        return $this->parts;
    }

    public function setParts(int $parts): static
    {
        $this->parts = $parts;

        return $this;
    }

    public function getVoteTime(): ?\DateTime
    {
        return $this->voteTime;
    }

    public function setVoteTime(\DateTime $voteTime): static
    {
        $this->voteTime = $voteTime;

        return $this;
    }

    public function getDecision(): ?string
    {
        return $this->decision;
    }

    public function setDecision(string $decision): static
    {
        $this->decision = $decision;

        return $this;
    }

    public function getPoint(): ?AGPoint
    {
        return $this->point;
    }

    public function setPoint(?AGPoint $point): static
    {
        $this->point = $point;

        return $this;
    }
}
