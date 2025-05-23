<?php

namespace App\Entity;

use App\Repository\MembreRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MembreRepository::class)]
class Membre
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $username = null;

    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    #[ORM\Column]
    private ?int $type = null;

    #[ORM\Column(length: 255)]
    private ?string $accessCode = null;

    #[ORM\Column]
    private ?int $parts = null;

    #[ORM\Column]
    private ?bool $present = false;

    #[ORM\Column(nullable: true)]
    private ?\DateTime $dateArrivee = null;

    #[ORM\Column]
    private ?bool $isRepresent = false;

    #[ORM\ManyToOne(targetEntity: self::class, inversedBy: 'procFor')]
    private ?self $procTo = null;

    /**
     * @var Collection<int, self>
     */
    #[ORM\OneToMany(targetEntity: self::class, mappedBy: 'procTo')]
    private Collection $procFor;

    /**
     * @var Collection<int, Vote>
     */
    #[ORM\OneToMany(targetEntity: Vote::class, mappedBy: 'user', orphanRemoval: true)]
    private Collection $votes;

    public function __construct()
    {
        $this->procFor = new ArrayCollection();
        $this->votes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): static
    {
        $this->username = $username;

        return $this;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    public function getType(): ?int
    {
        return $this->type;
    }

    public function setType(int $type): static
    {
        $this->type = $type;

        return $this;
    }
    
    public function getTypeText() {
        $types = [
            1 => 'Commune',
            2 => 'CPAS',
            3 => 'ASBL',
            4 => 'Province',
            5 => 'Region',
            6 => 'Privé'
        ];
        return $types[$this->type];
    }

    public function getAccessCode(): ?string
    {
        return $this->accessCode;
    }

    public function setAccessCode(string $accessCode): static
    {
        $this->accessCode = $accessCode;

        return $this;
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

    public function isPresent(): ?bool
    {
        return $this->present;
    }

    public function setPresent(bool $present): static
    {
        $this->present = $present;

        return $this;
    }

    public function getDateArrivee(): ?\DateTime
    {
        return $this->dateArrivee;
    }

    public function setDateArrivee(?\DateTime $dateArrivee): static
    {
        $this->dateArrivee = $dateArrivee;

        return $this;
    }

    public function isRepresent(): ?bool
    {
        return $this->isRepresent;
    }

    public function setIsRepresent(bool $isRepresent): static
    {
        $this->isRepresent = $isRepresent;

        return $this;
    }

    public function getProcTo(): ?self
    {
        return $this->procTo;
    }

    public function setProcTo(?self $procTo): static
    {
        $this->procTo = $procTo;

        return $this;
    }

    /**
     * @return Collection<int, self>
     */
    public function getProcFor(): Collection
    {
        return $this->procFor;
    }

    public function addProcFor(self $procFor): static
    {
        if (!$this->procFor->contains($procFor)) {
            $this->procFor->add($procFor);
            $procFor->setProcTo($this);
        }

        return $this;
    }

    public function removeProcFor(self $procFor): static
    {
        if ($this->procFor->removeElement($procFor)) {
            // set the owning side to null (unless already changed)
            if ($procFor->getProcTo() === $this) {
                $procFor->setProcTo(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Vote>
     */
    public function getVotes(): Collection
    {
        return $this->votes;
    }

    public function addVote(Vote $vote): static
    {
        if (!$this->votes->contains($vote)) {
            $this->votes->add($vote);
            $vote->setUser($this);
        }

        return $this;
    }

    public function removeVote(Vote $vote): static
    {
        if ($this->votes->removeElement($vote)) {
            // set the owning side to null (unless already changed)
            if ($vote->getUser() === $this) {
                $vote->setUser(null);
            }
        }

        return $this;
    }
}
