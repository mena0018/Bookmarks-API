<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Controller\GetAvatarController;
use App\Controller\GetMeController;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;


#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ApiResource(
    collectionOperations: [
        'get',
        'get_me' => [
            'method' => 'GET',
            'path'   => '/me',
            'controller' => GetMeController::class,
            "security" => "is_granted('ROLE_USER')",
            'normalization_context' => [
                'groups' => [
                    'get_Me',
                    'get_User'
                ]
            ],
            'pagination_enabled' => false,
            "openapi_context" => [
                'summary' => 'Accès aux informations personnelles',
                'description' => 'La route permet de retourner l id, le login, le nom,
                                  le prénom et le mail de l\'utilisateur connecté',
                'responses' =>[
                    '200' => [
                        'description' => 'Succès, les informations personnelles de l\'utilisateur sont retournées'
                    ],
                    '401' => [
                        'description' => 'Accès interdit, l\'utilisateur doit se connecter'
                    ]
                ]
            ]
        ],
    ],
    itemOperations: [
        'get'=> [
            'normalization_context' => ['groups' => ['get_User']]
        ],
        'put' => [
            'denormalization_context' => ['groups' => ['set_User']],
            "security" => "is_granted('ROLE_USER') and object == user",
            'normalization_context' => ['groups' => ['get_User', 'get_Me']]
        ],
        'patch' => [
            'denormalization_context' => ['groups' => ['set_User']],
            "security" => "is_granted('ROLE_USER') and object == user",
            'normalization_context' => ['groups' => ['get_User', 'get_Me']]
        ],
        'get_avatar' => [
            'method' => 'get',
            'path' => '/users/{id}/avatar',
            'controller' => GetAvatarController::class,
            "openapi_context" => [
                'content' => [
                    'image/png' => [
                        'schema' => [
                            'type' => 'string',
                            'format' => 'binary',
                        ]
                    ]
                ]
            ],
            'formats:' => [
                'png' => 'image/png',
            ]
        ],
    ]
)]
#[UniqueEntity('login')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(['get_User', 'set_User'])]
    private $id;

    #[ORM\Column(type: 'string', length: 180)]
    #[Groups(['get_User', 'set_User'])]
    #[Assert\Regex(
        pattern: '/^[^<,>,&,"]+$/',
        message: 'Caractère non autorisé',
    )]
    private $login;

    #[ORM\Column(type: 'json')]
    private $roles = [];

    #[ORM\Column(type: 'string')]
    #[Groups(['set_User'])]
    private $password;

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups(['get_User', 'set_User'])]
    #[Assert\Regex(
        pattern: '/^[^<,>,&,"]+$/',
        message: 'Caractère non autorisé',
    )]
    private $firstname;

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups(['get_User', 'set_User'])]
    #[Assert\Regex(
        pattern: '/^[^<,>,&,"]+$/',
        message: 'Caractère non autorisé',
    )]
    private $lastname;

    #[ORM\Column(type: 'blob')]
    private $avatar;

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups(['set_User', 'get_Me'])]
    #[Assert\Email(
        message: 'The email {{ value }} is not a valid email.',
    )]
    private $mail;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Rating::class, orphanRemoval: true)]
    private $ratings;

    public function __construct()
    {
        $this->ratings = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLogin(): ?string
    {
        return $this->login;
    }

    public function setLogin(string $login): self
    {
        $this->login = $login;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->login;
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
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(string $firstname): self
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(string $lastname): self
    {
        $this->lastname = $lastname;

        return $this;
    }

    public function getAvatar()
    {
        return $this->avatar;
    }

    public function setAvatar($avatar): self
    {
        $this->avatar = $avatar;

        return $this;
    }

    public function getMail(): ?string
    {
        return $this->mail;
    }

    public function setMail(string $mail): self
    {
        $this->mail = $mail;

        return $this;
    }

    /**
     * @return Collection|Rating[]
     */
    public function getRatings(): Collection
    {
        return $this->ratings;
    }

    public function addRating(Rating $rating): self
    {
        if (!$this->ratings->contains($rating)) {
            $this->ratings[] = $rating;
            $rating->setUser($this);
        }

        return $this;
    }

    public function removeRating(Rating $rating): self
    {
        if ($this->ratings->removeElement($rating)) {
            // set the owning side to null (unless already changed)
            if ($rating->getUser() === $this) {
                $rating->setUser(null);
            }
        }

        return $this;
    }
}
