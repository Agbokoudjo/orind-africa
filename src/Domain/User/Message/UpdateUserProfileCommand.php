<?php

declare(strict_types=1);

namespace App\Domain\User\Message;

use App\Domain\User\Enum\UserType;

/**
 * Commande asynchrone pour mettre à jour les champs de profil utilisateur 
 * (slug, canonical, password) suite à une persistance initiale ou une mise à jour.
 * @author AGBOKOUDJO Franck <internationaleswebservices@gmail.com>
 * @package <https://github.com/Agbokoudjo/>
 */
final readonly class UpdateUserProfileCommand
{
    public function __construct(
        private int|string $userId,
        private UserType $userType
    ) {}

    public function getUserId(): int|string
    {
        return $this->userId;
    }

    public function getUserType(): UserType
    {
        return $this->userType;
    }
}
