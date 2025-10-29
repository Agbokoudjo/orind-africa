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

namespace App\Application\Service;

use RuntimeException;
use InvalidArgumentException;
use App\Application\Bus\EventBusInterface;
use App\Domain\User\Model\BaseUserInterface;
use App\Domain\User\Service\Resolver\UserTypeResolver;
use App\Domain\User\Event\UserAccountEmailVerificationEvent;
use App\Domain\User\Exception\EmailAlreadyVerifiedException;
use App\Domain\User\Exception\InvalidTokenException;
use App\Domain\User\Service\Security\Hash\TokenHasherInterface;
use App\Domain\User\Service\Registry\UserManagerRegistryInterface;
use App\Domain\User\Service\Security\Generator\TokenGeneratorInterface;

/**
 * @author AGBOKOUDJO Franck <internationaleswebservices@gmail.com>
 * @package App\Application\Service
 */
final class SecureTokenService
{
    /**
     * Délai minimum entre deux générations de token (en secondes).
     * Protège contre les abus et le spam.
     */
    private const TOKEN_GENERATION_COOLDOWN = 60;
    
    public function __construct(
        private readonly TokenGeneratorInterface $tokenGenerator,
        private readonly TokenHasherInterface $tokenHasher,
        private readonly UserManagerRegistryInterface $userManagerRegistry,
        private readonly EventBusInterface $eventBusDispatcher
    ) {}

    /**
     * Génère et stocke un nouveau token de confirmation d'email.
     * 
     * Cette méthode :
     * 1. Valide l'utilisateur et ses données
     * 2. Génère un token cryptographiquement sécurisé
     * 3. Hashe le token pour stockage sécurisé
     * 4. Persiste les changements en base de données
     * 5. Déclenche un événement pour l'envoi d'email
     * 
     * Le token en clair est envoyé dans l'événement pour être inclus
     * dans l'email de vérification. Seul le hash est stocké en BDD.
     *
     * @param BaseUserInterface $user L'utilisateur pour lequel générer le token
     * @param int $length La longueur du token (par défaut 32 caractères)
     * 
     * @return void
     * 
     * @throws InvalidArgumentException Si l'utilisateur ou la longueur sont invalides
     * @throws RuntimeException Si la génération ou le stockage échoue
     * @throws UnresolvableUserTypeException Si le type d'utilisateur ne peut être déterminé
     * 
     * @author AGBOKOUDJO Franck <internationaleswebservices@gmail.com>
     * @package App\Application\Service
     */
    public function generateEmailConfirmationToken(
        BaseUserInterface $user,
        int $length=TokenGeneratorInterface::DEFAULT_EMAIL_TOKEN_LENGTH
        ): void
    {
        try {
            // 1. Validation des données utilisateur
            $this->validateUser($user);

            // 2. Vérification du rate limiting
            $this->enforceTokenGenerationCooldown($user);

            // 3. Génération du token en clair (cryptographiquement sécurisé)
            $plainToken = $this->tokenGenerator->generate($length);

            // 4. Hachage du token pour stockage sécurisé
            $hashedToken = $this->tokenHasher->hash($plainToken);

            // 5. Mise à jour de l'entité utilisateur
            $user->setConfirmationToken($hashedToken);
            $user->setTokenRequestedAt(new \DateTime()); //Car le type est date_time en BDD

            // 6. Récupération du gestionnaire approprié
            $userManager = $this->userManagerRegistry->getByUser($user);

            // 7. Persistance en base de données
            $userManager->save($user);

            // 8. Déclenchement de l'événement pour envoi d'email
            $event = new UserAccountEmailVerificationEvent(
                $user->getUsername(),
                $user->getEmail(),
                $user->getSlug(),
                UserTypeResolver::resolveFromUser($user),
                $plainToken
            );
            $this->eventBusDispatcher->dispatch($event);

        } catch (\Exception|RuntimeException|InvalidTokenException $exception) {
            throw $exception;
        }
    }

    /**
     * Valide que l'utilisateur peut recevoir un token de confirmation.
     *
     * @param BaseUserInterface $user L'utilisateur à valider
     * 
     * @throws InvalidArgumentException Si l'utilisateur est invalide
     */
    private function validateUser(BaseUserInterface $user): void
    {
        if (empty($user->getEmail())) {
            throw new InvalidArgumentException(
                'L\'utilisateur doit avoir une adresse email valide pour recevoir un token de confirmation.'
            );
        }

        if ($user->isIsEmailVerified()) {
            
            throw EmailAlreadyVerifiedException::forUser($user->getEmail());
        }
    }

    /**
     * Applique un rate limiting sur la génération de tokens.
     * 
     * Empêche les abus en limitant la fréquence de génération de tokens.
     *
     * @param BaseUserInterface $user L'utilisateur demandant un token
     * 
     * @throws RuntimeException Si le délai minimum n'est pas respecté
     */
    private function enforceTokenGenerationCooldown(BaseUserInterface $user): void
    {
        $lastRequestedAt = $user->getTokenRequestedAt();

        if ($lastRequestedAt === null) {
            // Première demande, OK
            return;
        }

        $now = new \DateTimeImmutable();
        $timeSinceLastRequest = $now->getTimestamp() - $lastRequestedAt->getTimestamp();

        if ($timeSinceLastRequest < self::TOKEN_GENERATION_COOLDOWN) {
            $remainingTime = self::TOKEN_GENERATION_COOLDOWN - $timeSinceLastRequest;

            throw new RuntimeException(
                sprintf(
                    'Veuillez patienter %d secondes avant de demander un nouveau lien de vérification.',
                    $remainingTime
                )
            );
        }
    }
}
