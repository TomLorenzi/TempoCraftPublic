<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 * @ORM\Table(name="`user`")
 * @UniqueEntity(fields={"email", "pseudo", "ip"}, message="L'email, le pseudo ou l'adresse IP est déjà utilisé")
 */
class User implements UserInterface
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     */
    private $email;

    /**
     * @ORM\Column(type="json")
     */
    private $roles = [];

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     */
    private $password;

    /**
     * @ORM\ManyToMany(targetEntity=Card::class, mappedBy="creator")
     */
    private $cards;

    /**
     * @ORM\OneToMany(targetEntity=Craft::class, mappedBy="creator")
     */
    private $crafts;

    /**
     * @ORM\Column(type="string", length=255, unique=true)
     */
    private $pseudo;

    /**
     * @ORM\Column(type="smallint")
     */
    private $ban;

    /**
     * @ORM\Column(type="string", length=50, unique=true)
     */
    private $ip;

    /**
     * @ORM\OneToMany(targetEntity=UpVote::class, mappedBy="user")
     */
    private $upVotes;

    /**
     * @ORM\OneToMany(targetEntity=Report::class, mappedBy="user")
     */
    private $reports;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $craftPerDay;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $votesPerDay;

    public function __construct()
    {
        $this->cards = new ArrayCollection();
        $this->crafts = new ArrayCollection();
        $this->roles = array('ROLE_USER');
        $this->ban = 0;
        $this->upVotes = new ArrayCollection();
        $this->reports = new ArrayCollection();
        $this->craftPerDay = 0;
        $this->votesPerDay = 0;
    }

    public function __toString()
    {
        return $this->pseudo;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUsername(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getPassword(): string
    {
        return (string) $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Returning a salt is only needed, if you are not using a modern
     * hashing algorithm (e.g. bcrypt or sodium) in your security.yaml.
     *
     * @see UserInterface
     */
    public function getSalt(): ?string
    {
        return null;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
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
            $card->addCreator($this);
        }

        return $this;
    }

    public function removeCard(Card $card): self
    {
        if ($this->cards->removeElement($card)) {
            $card->removeCreator($this);
        }

        return $this;
    }

    /**
     * @return Collection|Craft[]
     */
    public function getCrafts(): Collection
    {
        return $this->crafts;
    }

    public function addCraft(Craft $craft): self
    {
        if (!$this->crafts->contains($craft)) {
            $this->crafts[] = $craft;
            $craft->setCreator($this);
        }

        return $this;
    }

    public function removeCraft(Craft $craft): self
    {
        if ($this->crafts->removeElement($craft)) {
            // set the owning side to null (unless already changed)
            if ($craft->getCreator() === $this) {
                $craft->setCreator(null);
            }
        }

        return $this;
    }

    public function getPseudo(): ?string
    {
        return $this->pseudo;
    }

    public function setPseudo(string $pseudo): self
    {
        $this->pseudo = $pseudo;

        return $this;
    }

    public function getBan(): ?int
    {
        return $this->ban;
    }

    public function setBan(int $ban): self
    {
        $this->ban = $ban;

        return $this;
    }

    public function getIp(): ?string
    {
        return $this->ip;
    }

    public function setIp(string $ip): self
    {
        $this->ip = $ip;

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
            $upVote->setUser($this);
        }

        return $this;
    }

    public function removeUpVote(UpVote $upVote): self
    {
        if ($this->upVotes->removeElement($upVote)) {
            // set the owning side to null (unless already changed)
            if ($upVote->getUser() === $this) {
                $upVote->setUser(null);
            }
        }

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
            $report->setUser($this);
        }

        return $this;
    }

    public function removeReport(Report $report): self
    {
        if ($this->reports->removeElement($report)) {
            // set the owning side to null (unless already changed)
            if ($report->getUser() === $this) {
                $report->setUser(null);
            }
        }

        return $this;
    }

    public function getCraftPerDay(): ?int
    {
        return $this->craftPerDay;
    }

    public function setCraftPerDay(?int $craftPerDay): self
    {
        $this->craftPerDay = $craftPerDay;

        return $this;
    }

    public function getVotesPerDay(): ?int
    {
        return $this->votesPerDay;
    }

    public function setVotesPerDay(?int $votesPerDay): self
    {
        $this->votesPerDay = $votesPerDay;

        return $this;
    }
}
