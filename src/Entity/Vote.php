<?php

namespace App\Entity;

use App\Repository\VoteRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: VoteRepository::class)]
class Vote
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'votes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Membre $user = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?AGPoint $point = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?Membre
    {
        return $this->user;
    }

    public function setUser(?Membre $user): static
    {
        $this->user = $user;

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
