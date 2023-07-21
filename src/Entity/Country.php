<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\OpenApi\Model;
use App\Repository\CountryRepository;
use App\State\CountriesCollectionExcelStateProvider;
use App\State\Country\Processor\Accept;
use App\State\Country\Processor\CountryItemStateProcessor;
use App\State\Country\Processor\Visit;
use App\State\Country\Provider\CountryCollectionStateProvider;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[
ApiResource(
    operations: [
        new GetCollection(
            uriTemplate: '/countries/excel',
            //security: "is_granted('ROLE_USER')",
            formats: ["xlsx" => ["mimeType" => "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"]],
            //read: false,
            //"formats"={"csv"={"text/csv"}},
            //controller: YourExportController::class,
            provider: CountriesCollectionExcelStateProvider::class,
        ),
        new GetCollection(
            normalizationContext: ['groups' => 'readCountry'],
            security: "is_granted('ROLE_USER')",
            provider: CountryCollectionStateProvider::class,
        ),
        new Post(
            normalizationContext: ['groups' => 'readCountry'],
            denormalizationContext: ['groups' => 'writeCountry'],
            security: "is_granted('ROLE_USER')",
            processor: CountryItemStateProcessor::class
        ),
        new Put(
            normalizationContext: ['groups' => ''],
            denormalizationContext: ['groups' => 'writeCountry'],
            security: "is_granted('ROLE_ADMIN')",
        ),
        new Put(
            uriTemplate: '/country/{id}/accept',
            openapi: new Model\Operation(
                summary: 'Accept country',
                description: "Accepting the entry provided by the user"
            ),
            security: "is_granted('ROLE_ADMIN')",
            processor: Accept::class
        ),
        new Put(
            uriTemplate: '/country/{id}/visit',
            openapi: new Model\Operation(
                summary: 'Visiting the country',
                description: "Marking the country in which the user was"
            ),
            security: "is_granted('ROLE_USER')",
            processor: Visit::class,
        ),
        new Put(
            uriTemplate: '/country/{id}/file',
            formats: ["jpg" => ["mimeType" => "application/jpg"]],
            openapi: new Model\Operation(
                summary: 'Send Flag',
                description: "Accepting the entry provided by the user",
                requestBody:[

                ]
            ),
            security: "is_granted('ROLE_ADMIN')",
            processor: Accept::class,
        ),
        new Delete(
            security: "is_granted('ROLE_USER')",
        ),

    ],
    normalizationContext: ['groups' => ''],
    denormalizationContext: ['groups' => ''],

)]

/*
 * "openapi_context"={
 *                 "requestBody"={
 *                     "content"={
 *                         "multipart/form-data"={
 *                             "schema"={
 *                                 "type"="object",
 *                                 "properties"={
 *                                     "file"={
 *                                         "type"="string",
 *                                         "format"="binary"
 *                                     }
 *                                 }
 *                             }
 *                         }
 *                     }
 *                 }
 *             }},
 */
#[ORM\Entity(repositoryClass: CountryRepository::class)]
class Country
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Groups(['readCountry', 'writeCountry', 'readUser'])]
    #[ORM\Column(length: 20)]
    #[Assert\NotBlank]
    private ?string $name = null;

    #[ORM\Column(length: 20, nullable: true)]
    public ?string $flag = null;

    #[Groups(['visitCountry', 'readCountry'])]
    #[ORM\ManyToMany(targetEntity: User::class, mappedBy: 'country')]
    private Collection $users;

    #[Groups(['acceptCountry', 'readCountry'])]
    #[ORM\Column(nullable: true)]
    private ?bool $verified = null;

    #[Groups(['readCountry'])]
    private int $countUser;

    #[ORM\OneToMany(mappedBy: 'country', targetEntity: Language::class)]
    #[Groups(['readCountry'])]
    private Collection $languages;

    #[Groups(['writeCountry'])]
    private array $language = [];

    public function __construct($name)
    {
        $this->name = $name;
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

    public function addUser(User $user): static
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

    public function getFlag(): ?MediaObject
    {
        return $this->flag;
    }

    public function setFlag(?MediaObject $flag): static
    {
        $this->flag = $flag;

        return $this;
    }

    /**
     * @return array
     */
    public function getLanguage(): array
    {
        return $this->language;
    }

    /**
     * @param array $language
     */
    public function setLanguage(array $language): void
    {
        $this->language = $language;
    }

}
