<?php

declare(strict_types=1);

namespace App\Domain\User\Message;

use App\Domain\User\Enum\UserType;

/**
 * Commande asynchrone pour mettre à jour le mot de password
 * (password) suite à  une mise à jour de mots de passe.
 * @author AGBOKOUDJO Franck <internationaleswebservices@gmail.com>
 * @package <https://github.com/Agbokoudjo/>
 */

final  readonly class UpdatePasswordUserCommand
{
    public function __construct(
        private int|string $userId,
        private UserType $userType,
        private string $generatePlainPassword
    ) {}

    public function getUserId(): int|string
    {
        return $this->userId;
    }

    public function getUserType(): UserType
    {
        return $this->userType;
    }

    /**
     * Get the value of generatePlainPassword
     */
    public function getGeneratePlainPassword(): string
    {
            return $this->generatePlainPassword;
    }
}
