<?php

namespace App\Entity;

use App\Repository\BlogRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Serializer\Annotation\Groups;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Link;
use App\State\BlogByCategoryStateProvider;
use App\State\BlogBySlugStateProvider;
use ApiPlatform\Metadata\ApiProperty;

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
            //read: false
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
            //read: false
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

    #[Groups(['blogpost:read'])]
    #[ApiProperty(readable: true)]
    private ?string $name = null;

    #[Groups(['blogpost:read'])]
    private ?string $title = null;

    #[Groups(['blogpost:read'])]
    private ?string $text = null;

    #[Groups(['blogpost:read'])]
    private ?string $meta_desc = null;

    #[Groups(['blogpost:read'])]
    private ?string $short_desc = null;

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

    #[ApiProperty(readable: true)]
    #[Groups(['blogpost:read'])]
    private ?string $slug = null;

    #[Gedmo\Slug(fields: ['name_hu'])]
    #[ORM\Column(length: 255)]
    private ?string $slug_hu = null;

    #[Gedmo\Slug(fields: ['name_en'])]
    #[ORM\Column(length: 255)]
    private ?string $slug_en = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $name_hu = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $name_en = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $title_hu = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $title_en = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $text_hu = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $text_en = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $meta_desc_hu = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $meta_desc_en = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $short_dec_hu = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $short_desc_en = null;

    public function __construct()
    {
        $this->tags = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCategorySlug(): ?string
    {
        $this->category_slug = $this->getCategory()->getSlug();
        return $this->category_slug;
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

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getText(): ?string
    {
        return $this->text;
    }

    public function setText(?string $text): static
    {
        $this->text = $text;

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

    public function getShortDesc(): ?string
    {
        return $this->short_desc;
    }

    public function setShortDesc(?string $short_desc): static
    {
        $this->short_desc = $short_desc;

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

    public function setSlugEn(string $slug_en): static
    {
        $this->slug_en = $slug_en;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): static
    {
        $this->slug = $slug;

        return $this;
    }

    public function getNameHu(): ?string
    {
        return $this->name_hu;
    }

    public function setNameHu(?string $name_hu): static
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

    public function getTitleHu(): ?string
    {
        return $this->title_hu;
    }

    public function setTitleHu(?string $title_hu): static
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

    public function getTextHu(): ?string
    {
        return $this->text_hu;
    }

    public function setTextHu(?string $text_hu): static
    {
        $this->text_hu = $text_hu;

        return $this;
    }

    public function getTextEn(): ?string
    {
        return $this->text_en;
    }

    public function setTextEn(?string $text_en): static
    {
        $this->text_en = $text_en;

        return $this;
    }

    public function getMetaDescHu(): ?string
    {
        return $this->meta_desc_hu;
    }

    public function setMetaDescHu(?string $meta_desc_hu): static
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

    public function getShortDecHu(): ?string
    {
        return $this->short_dec_hu;
    }

    public function setShortDecHu(?string $short_dec_hu): static
    {
        $this->short_dec_hu = $short_dec_hu;

        return $this;
    }

    public function getShortDescEn(): ?string
    {
        return $this->short_desc_en;
    }

    public function setShortDescEn(?string $short_desc_en): static
    {
        $this->short_desc_en = $short_desc_en;

        return $this;
    }

}
