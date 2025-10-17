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

namespace App\Infrastructure\Persistance;

use App\Domain\User\BaseUserInterface;
use Doctrine\Persistence\ManagerRegistry;
use Sonata\Doctrine\Entity\BaseEntityManager;
use App\Domain\User\Service\CanonicalFieldsUpdaterInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

/**
 * @author AGBOKOUDJO Franck <internationaleswebservices@gmail.com>
 * @package <https://github.com/Agbokoudjo/>
 */
abstract class AbstractUserManager extends BaseEntityManager implements CustomUserManagerInterface{
   
    protected function __construct(
        string $fqcnClass,
        ManagerRegistry $registry,
        private CanonicalFieldsUpdaterInterface $canonicalFieldsUpdater,
        private UserPasswordHasherInterface $userPasswordHasher,
    ) {
        parent::__construct($fqcnClass, $registry);
    }

    abstract public function getFQCN():string;
    public function updatePassword(BaseUserInterface $user): void
    {
        if (!$user instanceof PasswordAuthenticatedUserInterface) {
            throw new \InvalidArgumentException(\sprintf('User must implement %s', PasswordAuthenticatedUserInterface::class));
        }
        
        $plainPassword = $user->getPlainPassword();

        if (null === $plainPassword) {
            return;
        }

        $password = $this->userPasswordHasher->hashPassword($user, $plainPassword);

        $user->setPassword($password);
        $user->eraseCredentials();
    }

    public function findUserByUsername(string $username): ?BaseUserInterface
    {
        return $this->findOneBy([
            'usernameCanonical' => $this->canonicalFieldsUpdater->canonicalizeUsername($username),
        ]);
    }

    public function findUserByEmail(string $email): ?BaseUserInterface
    {
        return $this->findOneBy([
            'emailCanonical' => $this->canonicalFieldsUpdater->canonicalizeEmail($email),
        ]);
    }

    public function findUserByUsernameOrEmail(string $usernameOrEmail): ?BaseUserInterface
    {
        if (1 === preg_match('/^.+\@\S+\.\S+$/', $usernameOrEmail)) {
            $user = $this->findUserByEmail($usernameOrEmail);
            if (null !== $user) {
                return $user;
            }
        }

        return $this->findUserByUsername($usernameOrEmail);
    }

    public function findUserByConfirmationToken(string $token): ?BaseUserInterface
    {
        return $this->findOneBy(['confirmationToken' => $token]);
    }
}