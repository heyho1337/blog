<?php

namespace App\Entity;

use App\Repository\CategoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Link;
use ApiPlatform\Metadata\ApiProperty;
use App\State\CategoryBySlugStateProvider;

#[ApiResource(
    normalizationContext: ['groups' => ['category:read']],
    cacheHeaders: [
        'max_age' => 3600,
        'shared_max_age' => 3600,
        'public' => true,
    ],
    security: "is_granted('ROLE_API')",
    operations: [
        new Get(),
        new GetCollection(),
        new Get(
            name: 'get_category_by_slug',
            uriTemplate: '/categories/slug/{slug}',
            uriVariables: [
                'slug' => new Link(),
            ],
            provider: CategoryBySlugStateProvider::class,
        ),
    ],
)]
#[ORM\Entity(repositoryClass: CategoryRepository::class)]
class Category
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['category:read'])]
    #[ApiProperty(identifier: true)]
    private ?int $id = null;

    private static string $currentLang = 'en';

    // JSON translation storage
    #[ORM\Column(type: Types::JSON)]
    #[Groups(['category:read'])]
    private array $name = [];

    #[ORM\Column(type: Types::JSON)]
    #[Groups(['category:read'])]
    private array $meta_desc = [];

    #[ORM\Column(type: Types::JSON)]
    #[Groups(['category:read'])]
    private array $title = [];

    #[ORM\Column(type: Types::JSON)]
    #[Groups(['category:read'])]
    private array $short_desc = [];

    #[ORM\Column(type: Types::JSON)]
    #[Groups(['category:read'])]
    private array $text = [];

    #[ORM\Column(type: Types::JSON)]
    #[Groups(['category:read'])]
    #[ApiProperty(readable: true)]
    private array $slug = [];

    // Standard columns
    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['category:read'])]
    private ?string $image = null;

    #[ORM\Column]
    #[Groups(['category:read'])]
    private ?\DateTimeImmutable $created_at = null;

    #[ORM\Column]
    #[Groups(['category:read'])]
    private ?\DateTimeImmutable $modified_at = null;

    /**
     * @var Collection<int, Blog>
     */
    #[ORM\OneToMany(targetEntity: Blog::class, mappedBy: 'category')]
    #[Groups(['category:read'])]
    private Collection $articles;

    #[ORM\Column]
    #[Groups(['category:read'])]
    private ?bool $active = null;

    public function __construct()
    {
        $this->articles = new ArrayCollection();
        $this->name = [];
        $this->meta_desc = [];
        $this->title = [];
        $this->short_desc = [];
        $this->text = [];
        $this->slug = [];
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getShortDescTranslations(): array
    {
        return $this->short_desc;
    }

    public function setShortDescTranslations(array $short_desc): static
    {
        $this->short_desc = $short_desc;
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

    public function getSlugTranslations(): array
    {
        return $this->slug;
    }

    public function setSlugTranslations(array $slug): static
    {
        $this->slug = $slug;
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
            $article->setCategory($this);
        }
        return $this;
    }

    public function removeArticle(Blog $article): static
    {
        if ($this->articles->removeElement($article)) {
            if ($article->getCategory() === $this) {
                $article->setCategory(null);
            }
        }
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
        return $this->getName() ?? '—';
    }
}
