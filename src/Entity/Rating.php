<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\RatingRepository;
use App\Validator\IsAuthenticatedUser;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;


#[ORM\Table(name: "rating")]
#[ORM\Entity(repositoryClass: RatingRepository::class)]
#[UniqueEntity(
    fields: ['user', 'bookmark']
)]
#[ApiResource(
    collectionOperations: [
        'get',
        'post' => [
            "security" => "is_granted('ROLE_USER')",
        ]],
    itemOperations: [
        'get',
        'patch' => [
            "security" => "is_granted('ROLE_USER') and object.getUser() == user",
        ],
        'put' => [
            "security" => "is_granted('ROLE_USER') and object.getUser() == user",
        ],
        'delete' => [
            "security" => "is_granted('ROLE_USER') and object.getUser() == user"
        ]
    ]
)]
class Rating
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\ManyToOne(targetEntity: Bookmark::class, inversedBy: 'ratings')]
    #[ORM\JoinColumn(nullable: false)]
    private $bookmark;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'ratings')]
    #[ORM\JoinColumn(nullable: false)]
    #[IsAuthenticatedUser]
    private $user;

    #[ORM\Column(type: 'smallint')]
    #[Assert\GreaterThanOrEqual(0)]
    #[Assert\LessThan(11)]
    private $value;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBookmark(): ?Bookmark
    {
        return $this->bookmark;
    }

    public function setBookmark(?Bookmark $bookmark): self
    {
        $this->bookmark = $bookmark;

        return $this;
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

    public function getValue(): ?int
    {
        return $this->value;
    }

    public function setValue(int $value): self
    {
        $this->value = $value;

        return $this;
    }
}
