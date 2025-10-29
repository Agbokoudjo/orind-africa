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

namespace App\Domain\User\Model;

use App\Domain\User\Enum\UserClass;

/**
 * @author AGBOKOUDJO Franck <internationaleswebservices@gmail.com>
 * @package <https://github.com/Agbokoudjo/>
 *
 */
final class LoggerLoginUser
{
    public function __construct(
        private readonly string $username,
        private readonly string $email,
        private readonly UserClass $userClass,
        private readonly string $lastLoginIp,
        private readonly \DateTimeInterface $createdAt
    ) {}

    public static function fromUser(
        BaseUserInterface $user, 
        string $baseRole,
        string $lastLoginIp
        ): self
    {
        return new self(
            $user->getUsername(),
            $user->getEmail(),
            UserClass::fromRole($baseRole),
            $lastLoginIp,
           $user->getLastLogin()
        );
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
