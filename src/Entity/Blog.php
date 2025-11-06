<?php

namespace App\Entity;

use App\Repository\BlogRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Link;
use App\State\BlogByCategoryStateProvider;
use App\State\BlogBySlugStateProvider;

#[ApiResource(
    normalizationContext: ['groups' => ['blogpost:read']],
    cacheHeaders: [
        'max_age' => 3600,
        'shared_max_age' => 3600,
        'public' => true,
    ],
    operations: [
        new Get(),
        new GetCollection(),
        new Get(
            name: 'get_blog_by_category',
            uriTemplate: '/blogs/category/{id}',
            uriVariables: [
                'id' => new Link(),
            ],
            provider: BlogByCategoryStateProvider::class,
        ),
        new Get(
            name: 'get_blog_by_slug',
            uriTemplate: '/blogs/slug/{slug}',
            uriVariables: [
                'slug' => new Link(
                    fromClass: Blog::class,
                    fromProperty: 'slug'
                ),
            ],
            provider: BlogBySlugStateProvider::class,
        ),
    ],
    security: "is_granted('ROLE_API')"
)]
#[ORM\Entity(repositoryClass: BlogRepository::class)]
class Blog
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['blogpost:read'])]
    private ?int $id = null;

    private static string $currentLang = 'en';

    // JSON translation storage
    #[ORM\Column(type: Types::JSON)]
    #[Groups(['blogpost:read'])]
    private array $name = [];

    #[ORM\Column(type: Types::JSON)]
    #[Groups(['blogpost:read'])]
    private array $title = [];

    #[ORM\Column(type: Types::JSON)]
    #[Groups(['blogpost:read'])]
    private array $text = [];

    #[ORM\Column(type: Types::JSON)]
    #[Groups(['blogpost:read'])]
    private array $meta_desc = [];

    #[ORM\Column(type: Types::JSON)]
    #[Groups(['blogpost:read'])]
    private array $short_desc = [];

    #[ORM\Column(type: Types::JSON)]
    #[Groups(['blogpost:read'])]
    private array $slug = [];

    // Standard columns
    #[Groups(['blogpost:read'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $image = null;

    #[ORM\Column]
    #[Groups(['blogpost:read'])]
    private ?\DateTimeImmutable $created_at = null;

    #[ORM\Column]
    #[Groups(['blogpost:read'])]
    private ?\DateTimeImmutable $modified_at = null;

    #[ORM\ManyToOne(inversedBy: 'articles', targetEntity: Category::class)]
    #[Groups(['blogpost:read'])]
    private ?Category $category = null;

    #[Groups(['blogpost:read'])]
    private ?string $category_slug = null;

    /**
     * @var Collection<int, Tag>
     */
    #[ORM\ManyToMany(targetEntity: Tag::class, inversedBy: 'articles')]
    #[Groups(['blogpost:read'])]
    private Collection $tags;

    #[ORM\Column]
    #[Groups(['blogpost:read'])]
    private ?bool $active = null;

    public function __construct()
    {
        $this->tags = new ArrayCollection();
        $this->name = [];
        $this->title = [];
        $this->text = [];
        $this->meta_desc = [];
        $this->short_desc = [];
        $this->slug = [];
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCategorySlug(): ?string
    {
        $this->category_slug = $this->getCategory()?->getSlug();
        return $this->category_slug;
    }

    // Smart getters/setters
    public function getName(?string $lang = null): ?string
    {
        $lang = $lang ?? self::$currentLang;
        return $this->name[$lang] ?? $this->name['en'] ?? null;
    }

    public function setName(?string $value, ?string $lang = null): static
    {
        $lang = $lang ?? self::$currentLang;
        $this->name[$lang] = $value;
        return $this;
    }

    public function getTitle(?string $lang = null): ?string
    {
        $lang = $lang ?? self::$currentLang;
        return $this->title[$lang] ?? $this->title['en'] ?? null;
    }

    public function setTitle(?string $value, ?string $lang = null): static
    {
        $lang = $lang ?? self::$currentLang;
        $this->title[$lang] = $value;
        return $this;
    }

    public function getText(?string $lang = null): ?string
    {
        $lang = $lang ?? self::$currentLang;
        return $this->text[$lang] ?? $this->text['en'] ?? null;
    }

    public function setText(?string $value, ?string $lang = null): static
    {
        $lang = $lang ?? self::$currentLang;
        $this->text[$lang] = $value;
        return $this;
    }

    public function getMetaDesc(?string $lang = null): ?string
    {
        $lang = $lang ?? self::$currentLang;
        return $this->meta_desc[$lang] ?? $this->meta_desc['en'] ?? null;
    }

    public function setMetaDesc(?string $value, ?string $lang = null): static
    {
        $lang = $lang ?? self::$currentLang;
        $this->meta_desc[$lang] = $value;
        return $this;
    }

    public function getShortDesc(?string $lang = null): ?string
    {
        $lang = $lang ?? self::$currentLang;
        return $this->short_desc[$lang] ?? $this->short_desc['en'] ?? null;
    }

    public function setShortDesc(?string $value, ?string $lang = null): static
    {
        $lang = $lang ?? self::$currentLang;
        $this->short_desc[$lang] = $value;
        return $this;
    }

    public function getSlug(?string $lang = null): ?string
    {
        $lang = $lang ?? self::$currentLang;
        return $this->slug[$lang] ?? $this->slug['en'] ?? null;
    }

    public function setSlug(?string $value, ?string $lang = null): static
    {
        $lang = $lang ?? self::$currentLang;
        $this->slug[$lang] = $value;
        return $this;
    }

    // Methods to get/set all translations
    public function getNameTranslations(): array
    {
        return $this->name;
    }

    public function setNameTranslations(array $name): static
    {
        $this->name = $name;
        return $this;
    }

    public function getTitleTranslations(): array
    {
        return $this->title;
    }

    public function setTitleTranslations(array $title): static
    {
        $this->title = $title;
        return $this;
    }

    public function getTextTranslations(): array
    {
        return $this->text;
    }

    public function setTextTranslations(array $text): static
    {
        $this->text = $text;
        return $this;
    }

    public function getMetaDescTranslations(): array
    {
        return $this->meta_desc;
    }

    public function setMetaDescTranslations(array $meta_desc): static
    {
        $this->meta_desc = $meta_desc;
        return $this;
    }

    public function getShortDescTranslations(): array
    {
        return $this->short_desc;
    }

    public function setShortDescTranslations(array $short_desc): static
    {
        $this->short_desc = $short_desc;
        return $this;
    }

    public function getSlugTranslations(): array
    {
        return $this->slug;
    }

    public function setSlugTranslations(array $slug): static
    {
        $this->slug = $slug;
        return $this;
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

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): static
    {
        $this->category = $category;
        return $this;
    }

    /**
     * @return Collection<int, Tag>
     */
    public function getTags(): Collection
    {
        return $this->tags;
    }

    public function addTag(Tag $tag): static
    {
        if (!$this->tags->contains($tag)) {
            $this->tags->add($tag);
        }
        return $this;
    }

    public function removeTag(Tag $tag): static
    {
        $this->tags->removeElement($tag);
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

    public static function setCurrentLang(string $lang): void
    {
        self::$currentLang = $lang;
    }

    public static function getCurrentLang(): string
    {
        return self::$currentLang;
    }

    public function __toString(): string
    {
        return $this->getName() ?? '';
    }
}
