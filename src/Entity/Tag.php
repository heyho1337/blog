<?php

namespace App\Entity;

use App\Repository\TagRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Serializer\Annotation\Groups;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;

#[ApiResource(
    normalizationContext: ['groups' => ['tag:read']],
    operations: [
        new Get(),
        new GetCollection()
    ],
    security: "is_granted('ROLE_API')"
)]

#[ORM\Entity(repositoryClass: TagRepository::class)]
class Tag
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['tag:read'])]
    private ?int $id = null;

    private static string $currentLang = 'en';

    #[Groups(['tag:read'])]
    private ?string $name = null;

    #[Groups(['tag:read'])]
    private ?string $slug = null;

    #[Groups(['tag:read'])]
    private ?string $meta_desc = null;

    #[Groups(['tag:read'])]
    private ?string $title = null;

    #[ORM\Column]
    #[Groups(['tag:read'])]
    private ?\DateTimeImmutable $created_at = null;

    #[ORM\Column]
    #[Groups(['tag:read'])]
    private ?\DateTimeImmutable $modified_at = null;

    /**
     * @var Collection<int, Blog>
     */
    #[ORM\ManyToMany(targetEntity: Blog::class, mappedBy: 'tags')]
    #[Groups(['tag:read'])]
    private Collection $articles;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $name_hu = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $name_en = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $slug_hu = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $slug_en = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $meta_desc_hu = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $meta_desc_en = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $title_hu = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $title_en = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $image = null;

    #[ORM\Column]
    private ?bool $active = null;

    public function __construct()
    {
        $this->articles = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(?string $slug): static
    {
        $this->slug = $slug;

        return $this;
    }

    public function getMetaDesc(): ?string
    {
        return $this->meta_desc;
    }

    public function setMetaDesc(?string $meta_desc): static
    {
        $this->meta_desc = $meta_desc;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeImmutable $created_at): static
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getModifiedAt(): ?\DateTimeImmutable
    {
        return $this->modified_at;
    }

    public function setModifiedAt(\DateTimeImmutable $modified_at): static
    {
        $this->modified_at = $modified_at;

        return $this;
    }

    /**
     * @return Collection<int, Blog>
     */
    public function getArticles(): Collection
    {
        return $this->articles;
    }

    public function addArticle(Blog $article): static
    {
        if (!$this->articles->contains($article)) {
            $this->articles->add($article);
            $article->addTag($this);
        }

        return $this;
    }

    public function removeArticle(Blog $article): static
    {
        if ($this->articles->removeElement($article)) {
            $article->removeTag($this);
        }

        return $this;
    }

    public function getNameHu(): ?string
    {
        return $this->name_hu;
    }

    public function setNameHu(string $name_hu): static
    {
        $this->name_hu = $name_hu;

        return $this;
    }

    public function getNameEn(): ?string
    {
        return $this->name_en;
    }

    public function setNameEn(?string $name_en): static
    {
        $this->name_en = $name_en;

        return $this;
    }

    public function getSlugHu(): ?string
    {
        return $this->slug_hu;
    }

    public function setSlugHu(string $slug_hu): static
    {
        $this->slug_hu = $slug_hu;

        return $this;
    }

    public function getSlugEn(): ?string
    {
        return $this->slug_en;
    }

    public function setSlugEn(?string $slug_en): static
    {
        $this->slug_en = $slug_en;

        return $this;
    }

    public function getMetaDescHu(): ?string
    {
        return $this->meta_desc_hu;
    }

    public function setMetaDescHu(string $meta_desc_hu): static
    {
        $this->meta_desc_hu = $meta_desc_hu;

        return $this;
    }

    public function getMetaDescEn(): ?string
    {
        return $this->meta_desc_en;
    }

    public function setMetaDescEn(?string $meta_desc_en): static
    {
        $this->meta_desc_en = $meta_desc_en;

        return $this;
    }

    public function getTitleHu(): ?string
    {
        return $this->title_hu;
    }

    public function setTitleHu(string $title_hu): static
    {
        $this->title_hu = $title_hu;

        return $this;
    }

    public function getTitleEn(): ?string
    {
        return $this->title_en;
    }

    public function setTitleEn(?string $title_en): static
    {
        $this->title_en = $title_en;

        return $this;
    }

    public static function setCurrentLang(string $lang): void
    {
        self::$currentLang = $lang;
    }

    public function __toString(): string
    {
        $getter = 'getName' . self::$currentLang;
        if (method_exists($this, $getter)) {
            return (string) $this->$getter();
        }

        return '';
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): static
    {
        $this->image = $image;

        return $this;
    }

    public function isActive(): ?bool
    {
        return $this->active;
    }

    public function setActive(bool $active): static
    {
        $this->active = $active;

        return $this;
    }

}
