<?php

namespace App\Entity;

use App\Repository\CardRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=CardRepository::class)
 */
class Card
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @ORM\ManyToMany(targetEntity=User::class, inversedBy="cards")
     */
    private $creator;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $image;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $imageId;

    /**
     * @ORM\ManyToOne(targetEntity=CardType::class, inversedBy="cards")
     */
    private $type;

    /**
     * @ORM\ManyToMany(targetEntity=Craft::class, mappedBy="cards")
     */
    private $crafts;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $lvl;

    /**
     * @ORM\ManyToMany(targetEntity=Level::class, mappedBy="cards")
     */
    private $levels;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private $category;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $golden;

    /**
     * @ORM\ManyToMany(targetEntity=Monster::class, inversedBy="cards")
     */
    private $monsters;

    public function __construct()
    {
        $this->creator = new ArrayCollection();
        $this->items = new ArrayCollection();
        $this->crafts = new ArrayCollection();
        $this->levels = new ArrayCollection();
        $this->monsters = new ArrayCollection();
    }

    public function __toString()
    {
        return $this->name;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return Collection|User[]
     */
    public function getCreator(): Collection
    {
        return $this->creator;
    }

    public function addCreator(User $creator): self
    {
        if (!$this->creator->contains($creator)) {
            $this->creator[] = $creator;
        }

        return $this;
    }

    public function removeCreator(User $creator): self
    {
        $this->creator->removeElement($creator);

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): self
    {
        $this->image = $image;

        return $this;
    }

    public function getImageId(): ?int
    {
        return $this->imageId;
    }

    public function setImageId(?int $imageId): self
    {
        $this->imageId = $imageId;

        return $this;
    }

    public function getType(): ?CardType
    {
        return $this->type;
    }

    public function setType(?CardType $type): self
    {
        $this->type = $type;

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
            $craft->addCard($this);
        }

        return $this;
    }

    public function removeCraft(Craft $craft): self
    {
        if ($this->crafts->removeElement($craft)) {
            $craft->removeCard($this);
        }

        return $this;
    }

    public function getLvl(): ?int
    {
        return $this->lvl;
    }

    public function setLvl(?int $lvl): self
    {
        $this->lvl = $lvl;

        return $this;
    }

    /**
     * @return Collection|Level[]
     */
    public function getLevels(): Collection
    {
        return $this->levels;
    }

    public function addLevel(Level $level): self
    {
        if (!$this->levels->contains($level)) {
            $this->levels[] = $level;
            $level->addCard($this);
        }

        return $this;
    }

    public function removeLevel(Level $level): self
    {
        if ($this->levels->removeElement($level)) {
            $level->removeCard($this);
        }

        return $this;
    }

    public function getCategory(): ?string
    {
        return $this->category;
    }

    public function setCategory(?string $category): self
    {
        $this->category = $category;

        return $this;
    }

    public function getGolden(): ?bool
    {
        return $this->golden;
    }

    public function setGolden(?bool $golden): self
    {
        $this->golden = $golden;

        return $this;
    }

    /**
     * @return Collection|Monster[]
     */
    public function getMonsters(): Collection
    {
        return $this->monsters;
    }

    public function addMonster(Monster $monster): self
    {
        if (!$this->monsters->contains($monster)) {
            $this->monsters[] = $monster;
        }

        return $this;
    }

    public function removeMonster(Monster $monster): self
    {
        $this->monsters->removeElement($monster);

        return $this;
    }
}
