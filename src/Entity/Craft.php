<?php

namespace App\Entity;

use App\Repository\CraftRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=CraftRepository::class)
 */
class Craft
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="crafts")
     * @ORM\JoinColumn(nullable=false)
     */
    private $creator;

    /**
     * @ORM\ManyToMany(targetEntity=Card::class, inversedBy="crafts")
     */
    private $cards;

    /**
     * @ORM\ManyToOne(targetEntity=Item::class, inversedBy="crafts")
     */
    private $item;

    /**
     * @ORM\OneToMany(targetEntity=Report::class, mappedBy="craft")
     */
    private $reports;

    /**
     * @ORM\OneToMany(targetEntity=UpVote::class, mappedBy="craft")
     */
    private $upVotes;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isVerified;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $date;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $isFalse;

    public function __construct()
    {
        $this->cards = new ArrayCollection();
        $this->reports = new ArrayCollection();
        $this->upVotes = new ArrayCollection();
        $this->isVerified = 0;
        $this->isFalse = 0;
        $this->date = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCreator(): ?User
    {
        return $this->creator;
    }

    public function setCreator(?User $creator): self
    {
        $this->creator = $creator;

        return $this;
    }

    /**
     * @return Collection|Card[]
     */
    public function getCards(): Collection
    {
        return $this->cards;
    }

    public function addCard(Card $card): self
    {
        if (!$this->cards->contains($card)) {
            $this->cards[] = $card;
        }

        return $this;
    }

    public function removeCard(Card $card): self
    {
        $this->cards->removeElement($card);

        return $this;
    }

    public function getItem(): ?Item
    {
        return $this->item;
    }

    public function setItem(?Item $item): self
    {
        $this->item = $item;

        return $this;
    }

    /**
     * @return Collection|Report[]
     */
    public function getReports(): Collection
    {
        return $this->reports;
    }

    public function addReport(Report $report): self
    {
        if (!$this->reports->contains($report)) {
            $this->reports[] = $report;
            $report->setCraft($this);
        }

        return $this;
    }

    public function removeReport(Report $report): self
    {
        if ($this->reports->removeElement($report)) {
            // set the owning side to null (unless already changed)
            if ($report->getCraft() === $this) {
                $report->setCraft(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|UpVote[]
     */
    public function getUpVotes(): Collection
    {
        return $this->upVotes;
    }

    public function addUpVote(UpVote $upVote): self
    {
        if (!$this->upVotes->contains($upVote)) {
            $this->upVotes[] = $upVote;
            $upVote->setCraft($this);
        }

        return $this;
    }

    public function removeUpVote(UpVote $upVote): self
    {
        if ($this->upVotes->removeElement($upVote)) {
            // set the owning side to null (unless already changed)
            if ($upVote->getCraft() === $this) {
                $upVote->setCraft(null);
            }
        }

        return $this;
    }

    public function getIsVerified(): ?bool
    {
        return $this->isVerified;
    }

    public function setIsVerified(bool $isVerified): self
    {
        $this->isVerified = $isVerified;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(?\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getIsFalse(): ?bool
    {
        return $this->isFalse;
    }

    public function setIsFalse(?bool $isFalse): self
    {
        $this->isFalse = $isFalse;

        return $this;
    }
}
