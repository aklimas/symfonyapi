<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\OpenApi\Model;
use App\Repository\CountryRepository;
use App\State\Country\Processor\CountryAccept;
use App\State\Country\Processor\CountryVisit;
use App\State\Country\Provider\CountryCollectionExportToExcel;
use App\State\Country\Provider\CountryCollectionStateProvider;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    operations: [
        new GetCollection(
            openapi: new Model\Operation(
                summary: 'Country Collection',
                description: 'A method that returns a list of countries with the number of visitors. The amount of data depends on the role.',
            ),
            normalizationContext: ['groups' => 'readCountry'],
            provider: CountryCollectionStateProvider::class,
        ),
        new GetCollection(
            uriTemplate: '/countries/excel',
            formats: ['xlsx' => ['mimeType' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']],
            openapi: new Model\Operation(
                summary: 'Method for exporting the list of countries to Excel (xlsx)',
                description: 'Method for exporting the list of countries to Excel (xlsx). The collection will return a list of countries with the number of visitors. Method available for ROLE_USER',
            ),
            provider: CountryCollectionExportToExcel::class,
        ),
        new Get(
            openapi: new Model\Operation(
                summary: 'Country Item',
                description: 'A method that returns one country with the number of visitors. The amount of data depends on the role.',
            ),
            normalizationContext: ['groups' => 'readCountry'],
            security: "is_granted('ROLE_USER')",
        ),
        new Post(
            openapi: new Model\Operation(
                summary: 'Add Country',
                description: 'The method for adding a country.',
            ),
            normalizationContext: ['groups' => 'readCountry'],
            denormalizationContext: ['groups' => 'writeCountry'],
            security: "is_granted('ROLE_USER')",
            processor: CountryAccept::class,
        ),
        new Put(
            openapi: new Model\Operation(
                summary: 'Update Country',
                description: 'The method to update the country. The update is available only to the administrator.',
            ),
            normalizationContext: ['groups' => 'readCountry'],
            denormalizationContext: ['groups' => 'writeCountry'],
            security: "is_granted('ROLE_ADMIN')",
        ),
        new Put(
            uriTemplate: '/countries/{id}/accept',
            openapi: new Model\Operation(
                responses: [
                    200 => [
                        'description' => '[]',
                        'content' => [],
                    ],
                ],
                summary: 'Accept country',
                description: 'Accepting the entry provided by the user'
            ),
            normalizationContext: ['groups' => []],
            denormalizationContext: ['groups' => 'acceptCountry'],
            security: "is_granted('ROLE_ADMIN')",
            processor: CountryAccept::class
        ),
        new Put(
            uriTemplate: '/countries/{id}/visit',
            openapi: new Model\Operation(
                responses: [
                    200 => [
                        'description' => '[]',
                        'content' => [],
                    ],
                ],
                summary: 'Visiting the country',
                description: 'Marking the country in which the user was'
            ),
            normalizationContext: ['groups' => []],
            security: "is_granted('ROLE_USER')",
            processor: CountryVisit::class,
        ),
        new Delete(
            openapi: new Model\Operation(
                summary: 'Delete Country',
                description: 'A method for removing a country using the hard delete method',
            ),
            security: "is_granted('ROLE_ADMIN')",
        ),
    ],
)]
#[ORM\Entity(repositoryClass: CountryRepository::class)]
class Country
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column]
    private ?int $id = null;

    #[Groups(['readCountry', 'writeCountry', 'readUser'])]
    #[ORM\Column(length: 20)]
    #[Assert\NotBlank]
    private ?string $name = null;

    #[Groups(['visitCountry', 'readCountry', 'writeCountry'])]
    #[ORM\ManyToMany(targetEntity: User::class, mappedBy: 'country')]
    private Collection $users;

    #[Groups(['acceptCountry', 'readCountry', 'acceptCountry'])]
    #[ORM\Column(nullable: true)]
    private ?bool $verified = null;

    #[ORM\ManyToOne(targetEntity: MediaObject::class)]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups('writeCountry')]
    #[ApiProperty(types: ['https://schema.org/image'])]
    public ?MediaObject $image = null;

    #[ORM\OneToMany(mappedBy: 'country', targetEntity: Language::class, cascade: ['persist'])]
    #[Groups(['readCountry', 'writeCountry'])]
    private Collection $languages;

    #[Groups(['readCountry'])]
    private int $countUser;

    public function __construct()
    {
        $this->users = new ArrayCollection();
        $this->languages = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(?User $user): static
    {
        if (!$this->users->contains($user)) {
            $this->users->add($user);
            $user->addCountry($this);
        }

        return $this;
    }

    public function removeUser(User $user): static
    {
        if ($this->users->removeElement($user)) {
            $user->removeCountry($this);
        }

        return $this;
    }

    public function isVerified(): ?bool
    {
        return $this->verified;
    }

    public function setVerified(?bool $verified): static
    {
        $this->verified = $verified;

        return $this;
    }

    public function getCountUser(): int
    {
        return $this->users->count();
    }

    /**
     * @return Collection<int, Language>
     */
    public function getLanguages(): Collection
    {
        return $this->languages;
    }

    public function addLanguage(Language $language): static
    {
        if (!$this->languages->contains($language)) {
            $this->languages->add($language);
            $language->setCountry($this);
        }

        return $this;
    }

    public function removeLanguage(Language $language): static
    {
        if ($this->languages->removeElement($language)) {
            // set the owning side to null (unless already changed)
            if ($language->getCountry() === $this) {
                $language->setCountry(null);
            }
        }

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }
}
