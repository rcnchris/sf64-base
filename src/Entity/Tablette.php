<?php

namespace App\Entity;

use App\Entity\Trait\{DateFieldTrait, IdFieldTrait};
use App\Repository\TabletteRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Sluggable\Handler\TreeSlugHandler;
use Symfony\Component\Validator\Constraints\Length;

#[ORM\Entity(repositoryClass: TabletteRepository::class)]
#[Gedmo\Tree(type: 'nested')]
class Tablette
{
    use IdFieldTrait, DateFieldTrait;

    public const ICON = 'hierarchy';

    #[ORM\Column(type: Types::STRING, length: 255)]
    #[Length(min: 3, minMessage: 'This value is too short. It should have {{ limit }} character or more.|This value is too short. It should have {{ limit }} characters or more.')]
    private ?string $name = null;

    #[ORM\Column(type: Types::STRING, length: 255, unique: true)]
    #[Gedmo\Slug(fields: ['name'])]
    #[Gedmo\SlugHandler(class: TreeSlugHandler::class, options: [
        'parentRelationField' => 'parent',
        'separator' => '-',
    ])]
    private ?string $slug = null;

    #[ORM\Column(type: Types::STRING, length: 50, nullable: true)]
    private ?string $icon = null;

    #[ORM\Column(type: Types::STRING, length: 7)]
    private string $color = '#000000';

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[Gedmo\TreeLeft]
    #[ORM\Column(name: 'lft', type: Types::INTEGER)]
    private ?int $lft = null;

    #[Gedmo\TreeRight]
    #[ORM\Column(name: 'rgt', type: Types::INTEGER)]
    private ?int $rgt = null;

    #[Gedmo\TreeLevel]
    #[ORM\Column(name: 'lvl', type: Types::INTEGER)]
    private ?int $lvl = null;

    #[Gedmo\TreeRoot]
    #[ORM\ManyToOne(targetEntity: Tablette::class)]
    #[ORM\JoinColumn(name: 'tree_root', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private $root;

    #[Gedmo\TreeParent]
    #[ORM\ManyToOne(targetEntity: Tablette::class, inversedBy: 'children', cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'parent_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private $parent;

    #[ORM\OneToMany(targetEntity: Tablette::class, mappedBy: 'parent', cascade: ['persist'])]
    #[ORM\OrderBy(['name' => 'ASC'])]
    private $children;

    public function __construct()
    {
        $this->children = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->name;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;
        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function getIcon(): ?string
    {
        return $this->icon;
    }

    public function setIcon(?string $icon): static
    {
        $this->icon = $icon;
        return $this;
    }

    public function getColor(): string
    {
        return $this->color;
    }

    public function setColor(string $color): static
    {
        $this->color = $color;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;
        return $this;
    }

    public function getLft(): ?int
    {
        return $this->lft;
    }

    public function setLft(int $lft): static
    {
        $this->lft = $lft;

        return $this;
    }

    public function getRgt(): ?int
    {
        return $this->rgt;
    }

    public function setRgt(int $rgt): static
    {
        $this->rgt = $rgt;

        return $this;
    }

    public function getLvl(): ?int
    {
        return $this->lvl;
    }

    public function setLvl(int $lvl): static
    {
        $this->lvl = $lvl;

        return $this;
    }

    public function getRoot(): ?self
    {
        return $this->root;
    }

    public function getParent(): ?self
    {
        return $this->parent;
    }

    public function setParent(?self $parent): self
    {
        $this->parent = $parent;
        return $this;
    }

    /**
     * @return Collection|self[]
     */
    public function getChildren(): Collection
    {
        return $this->children;
    }

    public function addChild(self $child): self
    {
        if (!$this->children->contains($child)) {
            $this->children[] = $child;
            $child->setParent($this);
        }
        return $this;
    }

    public function removeChild(self $child): self
    {
        if ($this->children->removeElement($child)) {
            if ($child->getParent() === $this) {
                $child->setParent(null);
            }
        }
        return $this;
    }
}
