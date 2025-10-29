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

use Doctrine\ORM\Mapping as ORM;
use App\Domain\User\Enum\UserClass;
use App\Domain\User\Model\LoggerLoginUser;
use App\Infrastructure\Doctrine\Entity\User\Repository\LoggerLoginUserEntityRepository;

/**
 * @author AGBOKOUDJO Franck <internationaleswebservices@gmail.com>
 * @package <https://github.com/Agbokoudjo/>
 *
 */
#[ORM\Entity(repositoryClass:LoggerLoginUserEntityRepository::class)]
#[ORM\Table(name: "logger_login_user")]
class LoggerLoginUserEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $id = null;

    #[ORM\Column(type: "string")]
    private string $username;

    #[ORM\Column(type: "string")]
    private string $email;

    #[ORM\Column(type: "string", enumType: UserClass::class)]
    private UserClass $userClass;

    #[ORM\Column(type: "datetime")]
    private \DateTimeInterface $createdAt;

    #[ORM\Column(type: 'string', options: ['default' => null], nullable: true)]
    protected ?string $lastLoginIp = null;

    private function __construct() {} // Doctrine

    public static function fromDomain(LoggerLoginUser $log): self
    {
        $entity = new self();
        $entity->username  = $log->getUsername();
        $entity->email     = $log->getEmail();
        $entity->userClass = $log->getUserClass();
        $entity->lastLoginIp=$log->getLastLoginIp();
        $entity->createdAt = $log->getCreatedAt();

        return $entity;
    }

    public function toDomain(): LoggerLoginUser
    {
        return new LoggerLoginUser(
            $this->username,
            $this->email,
            $this->userClass,
            $this->lastLoginIp,
            $this->createdAt
        );
    }

    /**
     * Get the value of id
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): string
    {
        return $this->username;
    }
    public function getEmail(): string
    {
        return $this->email;
    }
    public function getUserClass(): UserClass
    {
        return $this->userClass;
    }
    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    public function getLastLoginIp(): ?string
    {
        return $this->lastLoginIp;
    }

    public function setLastLoginIp(?string $lastLoginIp): void
    {
        $this->lastLoginIp = $lastLoginIp;
    }
}
