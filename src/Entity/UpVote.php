<?php

namespace App\Entity;

use App\Repository\UpVoteRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=UpVoteRepository::class)
 */
class UpVote
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="upVotes")
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity=Craft::class, inversedBy="upVotes")
     */
    private $craft;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getCraft(): ?Craft
    {
        return $this->craft;
    }

    public function setCraft(?Craft $craft): self
    {
        $this->craft = $craft;

        return $this;
    }
}
