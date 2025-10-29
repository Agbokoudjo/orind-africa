<?php

declare(strict_types=1);
/*
 * This file is part of the project by AGBOKOUDJO Franck.
 *
 * (c) AGBOKOUDJO Franck <internationaleswebservices@gmail.com>
 * Phone: +229 01 67 25 18 86
 * LinkedIn: https://www.linkedin.com/in/internationales-web-apps-services-120520193/
 * Github: https://github.com/Agbokoudjo/
 * Company: INTERNATIONALES WEB APPS & SERVICES
 *
 * For more information, please feel free to contact the author.
 */

namespace App\Infrastructure\Doctrine\Entity\User;

use libphonenumber\PhoneNumber;

use Doctrine\ORM\Mapping as ORM;
use App\Domain\User\Model\AbstractUser;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use App\Infrastructure\Doctrine\CustomUserInterface;
use App\Infrastructure\Doctrine\Entity\User\AdminUser;
use App\Infrastructure\Doctrine\Entity\User\MemberUser;

/**
 * @author AGBOKOUDJO Franck <internationaleswebservices@gmail.com>
 * @package <https://github.com/Agbokoudjo/>
 */
#[ORM\Entity]
#[ORM\Table(name: "sonata_abstract_user")]
#[ORM\InheritanceType("SINGLE_TABLE")]
#[ORM\DiscriminatorColumn(name: "type", type: "string")]
#[ORM\DiscriminatorMap([
    "member" => MemberUser::class,
    "admin"  => AdminUser::class,
])]
#[Vich\Uploadable]
abstract class SonataUser extends AbstractUser implements CustomUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue('IDENTITY')]
    #[ORM\Column(type: "integer")]
    protected int|string|null $id = null;
    #[ORM\Column(type: "string", length: 255, unique: true)]
    protected ?string $username = null;


    #[ORM\Column(type: "string", length: 200, unique: true)]
    protected ?string $email = null;

    #[ORM\Column(type: "json", options: ['jsonb' => true])]
    protected array $roles = [];

    #[ORM\Column(type: "string")]
    protected ?string $slug = null;

    #[ORM\Column(type: 'string', length: 255, unique: true, nullable: true)]
    protected ?string $usernameCanonical = null;

    #[ORM\Column(type: 'string', length: 200, unique: true, nullable: true)]
    protected ?string $emailCanonical = null;

    #[ORM\Column(type: 'boolean')]
    protected bool $enabled = false;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    protected ?string $salt = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    protected ?string $password = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    protected ?\DateTimeInterface $lastLogin = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    protected ?string $confirmationToken = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    protected ?\DateTimeInterface $tokenRequestedAt = null;

    #[ORM\Column(type: 'boolean', nullable: true)]
    protected bool $isEmailVerified = false;

    #[ORM\Column(type: 'datetime', nullable: true)]
    protected ?\DateTimeInterface $passwordRequestedAt = null;

    #[ORM\Column(type: "datetime_immutable", nullable: true)]
    protected ?\DateTimeImmutable $emailVerifiedAt = null;

    #[ORM\Column(type: "datetime_immutable")]
    protected ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: "datetime", nullable: true)]
    protected ?\DateTimeInterface $updatedAt = null;

    #[Vich\UploadableField(mapping: 'avatars', fileNameProperty: 'avatarName')]
    private ?File $avatarFile = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $avatarName = null;

    #[ORM\Column(type: 'string', nullable: true)]
    protected ?string $documentName;

    /**
     * le document passport ou carte d'identity nationales
     * @var File|null
     */
    #[Vich\UploadableField(
        mapping: 'document',
        fileNameProperty: 'documentName',
    )]
    protected ?File  $documentfile=null;

    #[ORM\Column(type: 'string',length: 200, nullable: true)]
    protected ?string $profile = null;

    #[ORM\Column(type: 'phone_number',length:80, nullable: true)]
    protected ?PhoneNumber $phone = null; // Peut être null si non fourni

    #[ORM\Column(type: 'string', length: 200, nullable: true)]
    protected ?string $country;

    /**
     * Get the value of profile
     */
    public function getProfile(): ?string
    {
        return $this->profile;
    }

    /**
     * Set the value of profile
     */
    public function setProfile(?string $profile): void 
    {
        $this->profile = $profile;
    }


    /**
     * Get the value of phone
     */
    public function getPhone(): ?PhoneNumber
    {
        return $this->phone;
    }

    /**
     * Set the value of phone
     */
    public function setPhone(?PhoneNumber $phone): void
    {
        $this->phone = $phone;
    }

    /**
     * Get the value of country
     */
    public function getCountry(): ?string
    {
        return $this->country;
    }

    /**
     * Set the value of country
     */
    public function setCountry(?string $country): void
    {
        $this->country = $country;
    }

    /**
     * Get the value of documentName
     */
    public function getDocumentName(): ?string
    {
        return $this->documentName;
    }

    /**
     * Set the value of documentName
     */
    public function setDocumentName(?string $documentName): void
    {
        $this->documentName = $documentName;
    }

    /**
     * Get the value of documentfile
     */
    public function getDocumentfile(): ?File
    {
        return $this->documentfile;
    }

    /**
     * Set the value of documentfile
     */
    public function setDocumentfile(?File $documentfile): void
    {
        $this->documentfile = $documentfile;
    }

    public function getAvatarFile(): ?File
    {
        return $this->avatarFile;
    }

    public function setAvatarFile(?File $avatarFile=null): void
    {
        $this->avatarFile = $avatarFile;
    }

    public function getAvatarName(): ?string
    {
        return $this->avatarName;
    }

    public function setAvatarName(?string $avatarName): void
    {
        $this->avatarName = $avatarName;
    }
}