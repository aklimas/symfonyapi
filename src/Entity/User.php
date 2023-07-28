<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\OpenApi\Model;
use App\Repository\UserRepository;
use App\State\User\Processor\UserDelete;
use App\State\User\Processor\UserPasswordHasher;
use App\State\User\Provider\UserPdfStateProvider;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    operations: [
        new Get(
            openapi: new Model\Operation(
                summary: 'User item',
                description: 'A method used to return the data of the selected user. The amount of data depends on the role.',
            ),
            normalizationContext: ['groups' => 'readUser'],
            security: "is_granted('ROLE_USER')",
        ),
        new Get(
            uriTemplate: '/user/{id}/pdf',
            formats: ['pdf' => ['mimeType' => 'application/pdf']],
            openapi: new Model\Operation(
                summary: 'User item in PDF',
                description: 'A method used to return the data of the selected user in PDF',
            ),
            normalizationContext: ['groups' => ''],
            security: "is_granted('ROLE_USER')",
            provider: UserPdfStateProvider::class,
        ),
        new GetCollection(
            openapi: new Model\Operation(
                summary: 'User Collection',
                description: 'A method for returning a list of users. The amount of data depends on the role.',
            ),
            normalizationContext: ['groups' => 'readUserCollection'],
            security: "is_granted('ROLE_USER')",
        ),
        new Post(
            openapi: new Model\Operation(
                summary: 'Create user',
                description: 'A method to create a user without verification. An email is sent with a confirmation link.',
            ),
            normalizationContext: ['groups' => 'readUser'],
            denormalizationContext: ['groups' => 'createUser'],
            processor: UserPasswordHasher::class,
        ),
        new Put(
            uriTemplate: '/user/update/{id}',
            openapi: new Model\Operation(
                summary: 'Update User',
                description: 'A method for updating selected user data.',
            ),
            normalizationContext: ['groups' => 'readUser'],
            denormalizationContext: ['groups' => 'updateUser'],
            security: "(is_granted('ROLE_ADMIN') or object == user)",
            processor: UserPasswordHasher::class
        ),
        new Delete(
            openapi: new Model\Operation(
                summary: 'Delete User',
                description: 'A method for removing a country using the soft delete method',
            ),
            denormalizationContext: ['groups' => 'deleteUser'],
            security: "is_granted('ROLE_ADMIN')",
            processor: UserDelete::class
        ),
    ],
)]
#[ORM\Entity(repositoryClass: UserRepository::class)]
#[UniqueEntity(fields: ['email'], message: 'There is already an account with this email')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['readUser', 'readUserCollection'])]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    #[Groups(['createUser', 'readUser', 'readUserCollection'])]
    #[Assert\NotBlank(
        message: 'Complete the e-mail address',
    )]
    #[Assert\Email(
        message: 'The email {{ value }} is not a valid email.',
        mode: 'strict'
    )]
    #[Assert\Length(
        max: 180,
        maxMessage: 'The email address may contain {{ limit }} characters',
    )]
    private ?string $email = null;

    #[ORM\Column]
    private array $roles = [];

    #[ORM\Column]
    #[Groups(['createUser', 'updateUser'])]
    #[Assert\Length(
        min: 8,
        minMessage: 'Your password must be at least {{ limit }} characters long.'
    )]
    #[Assert\Regex(
        pattern: '/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[-+_!@#$%^&*.,?]).*$/',
        message: 'Your password must contain at least one uppercase letter, one lowercase letter, one number, and one special character.'
    )]
    private ?string $password = null;

    #[ORM\Column(length: 30)]
    #[Groups(['readUser', 'readUserCollection', 'createUser', 'updateUser', 'readCountry'])]
    #[Assert\NotBlank(
        message: 'Required field',
    )]
    #[Assert\Length(
        min: 2,
        max: 30,
        minMessage: 'Minimum {{ limit }} characters',
        maxMessage: 'Maximum {{ limit }} characters',
    )]
    private ?string $firstName = null;

    #[ORM\Column(length: 30)]
    #[Groups(['readUser', 'readUserCollection', 'createUser', 'updateUser', 'readCountry'])]
    #[Assert\NotBlank(
        message: 'Required field',
    )]
    #[Assert\Length(
        min: 2,
        max: 30,
        minMessage: 'Minimum {{ limit }} characters',
        maxMessage: 'Maximum {{ limit }} characters',
    )]
    private ?string $lastName = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Groups(['createUser', 'updateUser', 'readUser', 'readUserCollection'])]
    #[Assert\NotBlank(
        message: 'Required field',
    )]
    private ?\DateTimeInterface $dateBirthday;

    #[ORM\Column(type: 'boolean')]
    #[Groups(['readUser', 'readUserCollection'])]
    private bool $isVerified = false;

    #[ORM\Column(length: 30)]
    #[Groups(['createUser', 'updateUser'])]
    #[Assert\NotBlank(
        message: 'Required field',
    )]
    #[Assert\Length(
        min: 2,
        max: 30,
        minMessage: 'Minimum {{ limit }} znaki',
        maxMessage: 'Maximum {{ limit }} znaków',
    )]
    private string $login;

    #[ORM\ManyToMany(targetEntity: Country::class, inversedBy: 'users')]
    #[Groups('readUser')]
    private Collection $country;

    #[ORM\Column(nullable: true, options: ['default' => 'false'])]
    #[Groups('deleteUser')]
    private ?bool $softDelete = null;

    #[Groups(['readUser', 'readUserCollection'])]
    private mixed $age;

    public function getAge(): mixed
    {
        return $this->age;
    }

    public function setAge(int $age): void
    {
        $this->age = $age;
    }

    public function __construct()
    {
        $this->country = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
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

    public function setRoles(array $roles): static
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

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): static
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): static
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getDateBirthday(): ?\DateTimeInterface
    {
        return $this->dateBirthday;
    }

    public function setDateBirthday(\DateTimeInterface $dateBirthday): static
    {
        $this->dateBirthday = $dateBirthday;

        return $this;
    }

    public function isVerified(): bool
    {
        return $this->isVerified;
    }

    public function setIsVerified(bool $isVerified): static
    {
        $this->isVerified = $isVerified;

        return $this;
    }

    public function getLogin(): ?string
    {
        return $this->login;
    }

    public function setLogin(string $login): static
    {
        $this->login = $login;

        return $this;
    }

    public function isIsVerified(): ?bool
    {
        return $this->isVerified;
    }

    public function isSoftDelete(): ?bool
    {
        return $this->softDelete;
    }

    public function setSoftDelete(?bool $softDelete): static
    {
        $this->softDelete = $softDelete;

        return $this;
    }

    /**
     * @return Collection<int, Country>
     */
    public function getCountry(): Collection
    {
        return $this->country;
    }

    public function addCountry(Country $country): static
    {
        if (!$this->country->contains($country)) {
            $this->country->add($country);
        }

        return $this;
    }

    public function removeCountry(Country $country): static
    {
        $this->country->removeElement($country);

        return $this;
    }
}
