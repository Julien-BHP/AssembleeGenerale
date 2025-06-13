<?php

namespace App\Entity;

use App\Repository\AGPointRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AGPointRepository::class)]
class AGPoint
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $titre = null;

    #[ORM\Column]
    private ?int $ordre = null;

    #[ORM\Column]
    private ?bool $hasVote = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTime $voteOpen = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTime $voteClose = null;

    #[ORM\ManyToOne(inversedBy: 'points')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Seance $seance = null;

    /**
     * @var Collection<int, VoteAnno>
     */
    #[ORM\OneToMany(targetEntity: VoteAnno::class, mappedBy: 'point')]
    private Collection $voteAnnos;

    #[ORM\ManyToOne(targetEntity: self::class, inversedBy: 'sousPoints')]
    private ?self $parent = null;

    /**
     * @var Collection<int, self>
     */
    #[ORM\OneToMany(targetEntity: self::class, mappedBy: 'parent')]
    private Collection $sousPoints;

    public function __construct()
    {
        $this->voteAnnos = new ArrayCollection();
        $this->sousPoints = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitre(): ?string
    {
        return $this->titre;
    }

    public function setTitre(string $titre): static
    {
        $this->titre = $titre;

        return $this;
    }

    public function getOrdre(): ?int
    {
        return $this->ordre;
    }

    public function setOrdre(int $ordre): static
    {
        $this->ordre = $ordre;

        return $this;
    }

    public function hasVote(): ?bool
    {
        return $this->hasVote;
    }

    public function setHasVote(bool $hasVote): static
    {
        $this->hasVote = $hasVote;

        return $this;
    }

    public function getVoteOpen(): ?\DateTime
    {
        return $this->voteOpen;
    }

    public function setVoteOpen(?\DateTime $voteOpen): static
    {
        $this->voteOpen = $voteOpen;

        return $this;
    }

    public function getVoteClose(): ?\DateTime
    {
        return $this->voteClose;
    }

    public function setVoteClose(?\DateTime $voteClose): static
    {
        $this->voteClose = $voteClose;

        return $this;
    }

    public function getSeance(): ?Seance
    {
        return $this->seance;
    }

    public function setSeance(?Seance $seance): static
    {
        $this->seance = $seance;

        return $this;
    }

    /**
     * @return Collection<int, VoteAnno>
     */
    public function getVoteAnnos(): Collection
    {
        return $this->voteAnnos;
    }

    public function addVoteAnno(VoteAnno $voteAnno): static
    {
        if (!$this->voteAnnos->contains($voteAnno)) {
            $this->voteAnnos->add($voteAnno);
            $voteAnno->setPoint($this);
        }

        return $this;
    }

    public function removeVoteAnno(VoteAnno $voteAnno): static
    {
        if ($this->voteAnnos->removeElement($voteAnno)) {
            // set the owning side to null (unless already changed)
            if ($voteAnno->getPoint() === $this) {
                $voteAnno->setPoint(null);
            }
        }

        return $this;
    }

    public function getParent(): ?self
    {
        return $this->parent;
    }

    public function setParent(?self $parent): static
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * @return Collection<int, self>
     */
    public function getSousPoints(): Collection
    {
        return $this->sousPoints;
    }

    public function addSousPoint(self $sousPoint): static
    {
        if (!$this->sousPoints->contains($sousPoint)) {
            $this->sousPoints->add($sousPoint);
            $sousPoint->setParent($this);
        }

        return $this;
    }

    public function removeSousPoint(self $sousPoint): static
    {
        if ($this->sousPoints->removeElement($sousPoint)) {
            // set the owning side to null (unless already changed)
            if ($sousPoint->getParent() === $this) {
                $sousPoint->setParent(null);
            }
        }

        return $this;
    }
}
