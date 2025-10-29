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

namespace App\Domain\User\Event;

use App\Domain\User\Enum\UserType;

final readonly class UserAccountEmailVerificationEvent{

    public function __construct(
        private string $username,
        private string $email,
        private string $slug,
        private UserType $userType,
        private string $rawToken 
    ){}
  
    public function getUsername(): string
    {
        return $this->username;
    }
   
    public function getEmail(): string
    {
        return $this->email;
    }
   
    public function getSlug(): string
    {
        return $this->slug;
    }

    public function getRawToken(): string
    {
        return $this->rawToken;
    }

    public function getUserType(): UserType
    {
        return $this->userType;
    }
}