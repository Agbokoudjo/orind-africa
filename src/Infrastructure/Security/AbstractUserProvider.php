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

namespace App\Infrastructure\Security;

use App\Domain\User\BaseUserInterface;
use App\Infrastructure\Persistance\CustomUserManagerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\UserInterface as SecurityUserInterface;

/**
 * @author AGBOKOUDJO Franck <internationaleswebservices@gmail.com>
 * @package <https://github.com/Agbokoudjo/>
 */
abstract class AbstractUserProvider implements UserProviderInterface
{
    public function __construct(private CustomUserManagerInterface $userManager) {}

    /**
     * @param string $username
     */
    public function loadUserByUsername($username): SecurityUserInterface
    {
        return $this->loadUserByIdentifier($username);
    }

    public function loadUserByIdentifier(string $identifier): SecurityUserInterface
    {
        $user = $this->findUser($identifier);

        if (null === $user || !$user->isEnabled()) {
            throw new UserNotFoundException(\sprintf('Username "%s" does not exist.', $identifier));
        }

        if (!$user instanceof UserInterface) {
            throw new UnsupportedUserException(\sprintf('Expected an instance of %s, but got "%s".', UserInterface::class, $user::class));
        }

        return $user;
    }

    public function refreshUser(UserInterface $user): SecurityUserInterface
    {
        if (!$user instanceof UserInterface) {
            throw new UnsupportedUserException(\sprintf('Expected an instance of %s, but got "%s".', UserInterface::class, $user::class));
        }

        if (!$this->supportsClass($user::class)) {
            throw new UnsupportedUserException(\sprintf('Expected an instance of %s, but got "%s".', $this->userManager->getClass(), $user::class));
        }

        if(!$user instanceof BaseUserInterface){
            throw new UnsupportedUserException(\sprintf('User must implement %s', BaseUserInterface::class));
        }
        if (null === $reloadedUser = $this->userManager->findOneBy(['id' => $user->getId()])) {
            throw new UserNotFoundException(\sprintf('User with ID "%s" could not be reloaded.', $user->getId() ?? ''));
        }

        return $reloadedUser;
    }

    /**
     * @param string $class
     */
    public function supportsClass($class): bool
    {
        $userClass = $this->userManager->getClass();

        return $userClass === $class || is_subclass_of($class, $userClass);
    }

    private function findUser(string $username): ?BaseUserInterface
    {
        return $this->userManager->findUserByUsernameOrEmail($username);
    }
}
