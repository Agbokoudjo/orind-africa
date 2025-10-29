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

namespace App\Infrastructure\Service\Security;

use App\Application\Service\SecureTokenService;
use Psr\Log\LoggerInterface;
use App\Domain\User\Enum\UserType;
use App\Domain\User\Model\BaseUserInterface;
use App\Domain\User\Model\SimpleUserInterface;
use App\Domain\User\Manager\UserManagerInterface;
use App\Domain\User\Exception\InvalidTokenException;
use App\Domain\User\Exception\EmailAlreadyVerifiedException;
use App\Domain\User\Service\Security\Hash\TokenHasherInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use App\Domain\User\Service\Security\EmailVerificationInterface;
use App\Domain\User\Service\Registry\UserManagerRegistryInterface;
use App\Domain\User\Service\Security\Generator\TokenGeneratorInterface;

/**
 * Service de vérification des emails utilisateurs.
 * 
 * Gère la vérification des tokens de confirmation d'email envoyés aux utilisateurs
 * lors de leur inscription. Vérifie l'expiration et la validité du token avant
 * d'activer le compte.
 *
 * @author AGBOKOUDJO Franck <internationaleswebservices@gmail.com>
 * @package App\Infrastructure\Service
 */
final class EmailVerificationService implements EmailVerificationInterface
{
    public function __construct(
        private readonly UserManagerRegistryInterface $userManagerRegistry,
        private readonly TokenHasherInterface $tokenHasher,
        private readonly TokenGeneratorInterface $tokenGenerator,
        private EventDispatcherInterface $dispatcher ,
        private SecureTokenService $secureTokenService,
        private readonly ?LoggerInterface $logger = null
    ) {}

    /**
     * {@inheritdoc}
     * 
     * @throws InvalidTokenException Si le token est invalide, expiré ou déjà utilisé
     */
    public function verifyEmail(string $rawToken, string $slug, UserType $userType): void
    {
        $this->logger?->info('Tentative de vérification d\'email', [
            'slug' => $slug,
            'user_type' => $userType->value,
        ]);

        // 1. Récupération de l'utilisateur
        $userManager = $this->userManagerRegistry->getByUserType($userType);
        $user = $userManager->findUserBySlug($slug);

        if ($user === null) {
            $this->logger?->warning('Utilisateur non trouvé pour la vérification d\'email', [
                'slug' => $slug,
                'user_type' => $userType->value,
            ]);

            throw InvalidTokenException::userNotFound();
        }

        // 2. Récupération des données de token
        $hashedToken = $user->getConfirmationToken();
        $requestedAt = $user->getTokenRequestedAt();

        // 3. Vérification que le token existe
        if ($hashedToken === null || $requestedAt === null) {
            $this->logger?->info('Token manquant ou compte déjà vérifié', [
                'user_id' => $user->getId(),
                'has_token' => $hashedToken !== null,
                'has_requested_at' => $requestedAt !== null,
            ]);

            throw InvalidTokenException::alreadyUsedToken(
                $user->getEmailVerifiedAt() ?? new \DateTimeImmutable()
            );
        }

        //  Vérification de l'expiration du token
        if ($this->isTokenExpired($requestedAt)) {
            $expiresAt = $this->calculateExpirationDate($requestedAt);
            $this->logger?->warning('Token de vérification expiré', [
                'user_id' => $user->getId(),
                'requested_at' => $requestedAt->format('Y-m-d H:i:s'),
                'expires_at' => $expiresAt->format('Y-m-d H:i:s')
            ]);

            throw InvalidTokenException::expiredToken($expiresAt);
        }
        // Vérification de la validité du token (comparaison hash)
        if (!$this->tokenHasher->verify($rawToken, $hashedToken)) {
            $this->logger?->error('Token de vérification invalide (hash mismatch)', [
                'user_id' => $user->getId(),
                'slug' => $slug,
            ]);

            throw InvalidTokenException::invalidToken('Hash mismatch');
        }

        // ✅ Vérifier si rehash nécessaire
        if ($this->tokenHasher->needsRehash($hashedToken)) {
            $this->logger?->info('Rehashing token avec paramètres plus récents');
        }

        // 7. Application des changements (logique métier)
        $this->applyEmailVerification($user, $userManager);

        $this->logger?->info('Email vérifié avec succès', [
            'user_id' => $user->getId(),
            'email' => $user->getEmail(),
            'user_type' => $userType->value,
            'auto_enabled' => $user instanceof SimpleUserInterface,
        ]);
    }

   
    /**
     * Vérifie si un token a expiré.
     *
     * @param \DateTimeInterface $requestedAt Date de création du token
     * 
     * @return bool True si expiré, false sinon
     */
    private function isTokenExpired(\DateTimeInterface $requestedAt): bool
    {
        $now = new \DateTimeImmutable();
        $expirationTimestamp = $requestedAt->getTimestamp() + self::TOKEN_LIFETIME;

        return $now->getTimestamp() > $expirationTimestamp;
    }

    /**
     * Calcule la date d'expiration d'un token.
     *
     * @param \DateTimeInterface $requestedAt Date de création du token
     * 
     * @return \DateTimeImmutable Date d'expiration
     */
    private function calculateExpirationDate(\DateTimeInterface $requestedAt): \DateTimeImmutable
    {
        $expirationTimestamp = $requestedAt->getTimestamp() + self::TOKEN_LIFETIME;

        return (new \DateTimeImmutable())->setTimestamp($expirationTimestamp);
    }

    public function resendVerificationEmail(string $slug, UserType $userTypeEnum): void
    {
        $this->logger?->info('Demande de renvoi d\'email de vérification', [
            'slug' => $slug,
            'user_type' => $userTypeEnum->value,
        ]);

        // 1. Récupération de l'utilisateur
        $userManager = $this->userManagerRegistry->getByUserType($userTypeEnum);
        $user = $userManager->findUserBySlug($slug);

        if ($user === null) {
            $this->logger?->warning('Utilisateur non trouvé pour renvoi de vérification', [
                'slug' => $slug,
                'user_type' => $userTypeEnum->value,
            ]);
            throw InvalidTokenException::userNotFound();
        }

        // 2. Vérification si l'email est déjà vérifié
        // NOTE: SecureTokenService gère aussi cette vérification, mais il est mieux 
        // de la garder ici pour retourner une exception personnalisée de l'Application.
        if ($user->isIsEmailVerified()) {
            $this->logger?->info('Tentative de renvoi pour email déjà vérifié', [
                'user_id' => $user->getId(),
                'email' => $user->getEmail(),
            ]);
            throw EmailAlreadyVerifiedException::forUser($user->getEmail());
        }

        // --- 3. DÉLÉGATION AU SERVICE EXPERT ---
        try {
            // Le service s'occupe de tout : cooldown, génération, hachage, persistance et dispatch de l'événement.
            $this->secureTokenService->generateEmailConfirmationToken($user);

            $this->logger?->info('Email de vérification renvoyé avec succès', [
                'user_id' => $user->getId(),
                'email' => $user->getEmail(),
            ]);
        } catch (\RuntimeException $e) {
            // Le SecureTokenService lève RuntimeException pour le Cool-down.
            // Nous la relançons pour informer l'utilisateur de l'attente.
            $this->logger?->notice('Renvoi de token bloqué par le cooldown', [
                'user_id' => $user->getId(),
                'message' => $e->getMessage(),
            ]);
            throw $e;
        } catch (\Exception $e) {
            // Log toutes les autres erreurs imprévues (génération, hachage, persistance)
            $this->logger?->error('Échec critique du renvoi d\'email de vérification', [
                'user_id' => $user->getId(),
                'error' => $e->getMessage(),
            ]);
            // Relancer une RuntimeException générique pour l'utilisateur
            throw new \RuntimeException(
                'Impossible de renvoyer l\'email de vérification. Veuillez réessayer plus tard.',
                0,
                $e
            );
        }
    }

    /**
     * Applique les changements de vérification d'email à l'utilisateur.
     *
     * @param  BaseUserInterface $user L'utilisateur dont l'email a été vérifié
     * @param  UserManagerInterface $userManager Le gestionnaire d'utilisateurs pour la persistance
     * 
     * @return void
     */
    private function applyEmailVerification(
        BaseUserInterface $user,
        UserManagerInterface $userManager
    ): void {
        // Marquer l'email comme vérifié
        $user->setIsEmailVerified(true);
        $user->setEmailVerifiedAt(new \DateTimeImmutable());

        // Nettoyer les données de token
        $user->setConfirmationToken(null);
        $user->setTokenRequestedAt(null);

        // Activation automatique pour les utilisateurs simples (Client, Simple)
        // Les utilisateurs de type Admin/Member nécessitent une activation manuelle
        // par un fondateur ou super-administrateur
        if ($user instanceof SimpleUserInterface) {
            $user->setEnabled(true);

            $this->logger?->debug('Compte activé automatiquement après vérification', [
                'user_id' => $user->getId(),
            ]);
        } else {
            $this->logger?->debug('Compte non activé automatiquement (nécessite validation admin)', [
                'user_id' => $user->getId(),
            ]);
        }
        // Persistance des changements
        $userManager->save($user);
    }
}
