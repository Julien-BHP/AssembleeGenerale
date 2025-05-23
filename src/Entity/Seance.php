<?php

namespace App\Entity;

use App\Repository\SeanceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SeanceRepository::class)]
class Seance
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?\DateTime $date = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTime $debut = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTime $fin = null;

    #[ORM\Column]
    private ?bool $canPresent = true;

    #[ORM\Column(nullable: true)]
    private ?\DateTime $closePresent = null;

    /**
     * @var Collection<int, AGPoint>
     */
    #[ORM\OneToMany(targetEntity: AGPoint::class, mappedBy: 'seance', orphanRemoval: true)]
    private Collection $points;

    #[ORM\Column]
    private ?bool $extra = false;

    public function __construct()
    {
        $this->points = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDate(): ?\DateTime
    {
        return $this->date;
    }

    public function setDate(\DateTime $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function getDebut(): ?\DateTime
    {
        return $this->debut;
    }

    public function setDebut(?\DateTime $debut): static
    {
        $this->debut = $debut;

        return $this;
    }

    public function getFin(): ?\DateTime
    {
        return $this->fin;
    }

    public function setFin(?\DateTime $fin): static
    {
        $this->fin = $fin;

        return $this;
    }

    public function isCanPresent(): ?bool
    {
        return $this->canPresent;
    }

    public function setCanPresent(bool $canPresent): static
    {
        $this->canPresent = $canPresent;

        return $this;
    }

    public function getClosePresent(): ?\DateTime
    {
        return $this->closePresent;
    }

    public function setClosePresent(?\DateTime $closePresent): static
    {
        $this->closePresent = $closePresent;

        return $this;
    }

    /**
     * @return Collection<int, AGPoint>
     */
    public function getPoints(): Collection
    {
        return $this->points;
    }

    public function addPoint(AGPoint $point): static
    {
        if (!$this->points->contains($point)) {
            $this->points->add($point);
            $point->setSeance($this);
        }

        return $this;
    }

    public function removePoint(AGPoint $point): static
    {
        if ($this->points->removeElement($point)) {
            // set the owning side to null (unless already changed)
            if ($point->getSeance() === $this) {
                $point->setSeance(null);
            }
        }

        return $this;
    }

    public function isExtra(): ?bool
    {
        return $this->extra;
    }

    public function setExtra(bool $extra): static
    {
        $this->extra = $extra;

        return $this;
    }
}
