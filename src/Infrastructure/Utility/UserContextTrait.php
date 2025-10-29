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

namespace App\Infrastructure\Utility;

use App\Domain\User\Model\BaseUserInterface;
use App\Domain\User\Service\Resolver\UserTypeResolver;
use App\Domain\User\Exception\UnresolvableUserTypeException;

/**
 * @author AGBOKOUDJO Franck <internationaleswebservices@gmail.com>
 * @package <https://github.com/Agbokoudjo/>
 */
trait UserContextTrait{

    /**
     * Extrait le contexte utilisateur pour le logging.
     *
     * @param BaseUserInterface|null $user L'utilisateur (null si guest)
     * 
     * @return array<string, mixed> Le contexte utilisateur
     */
    protected function extractUserContext(?BaseUserInterface $user): array
    {
        if ($user === null) {
            return [
                'id' => null,
                'email' => 'anonymous',
                'username' => 'anonymous',
                'role' => 'ROLE_ANONYMOUS',
                'type' => 'guest',
            ];
        }

        try {
            $userType = UserTypeResolver::resolveFromUser($user);

            return [
                'id' => $user->getId(),
                'email' => method_exists($user, 'getEmail') ? $user->getEmail() : 'N/A',
                'username' => $user->getUsername(),
                'role' => $user->getRolePrincipal(),
                'type' => $userType->value,
            ];
        } catch (UnresolvableUserTypeException $e) {
            $this->logger?->warning('Impossible de résoudre le type utilisateur', [
                'user_class' => $user::class,
                'error' => $e->getMessage(),
            ]);

            return [
                'id' => $user->getId(),
                'email' => method_exists($user, 'getEmail') ? $user->getEmail() : 'N/A',
                'username' => $user->getUsername(),
                'role' => $user->getRolePrincipal(),
                'type' => 'unknown',
            ];
        }
    }
}