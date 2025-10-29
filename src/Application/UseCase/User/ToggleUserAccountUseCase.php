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

namespace App\Application\UseCase\User;

use App\Domain\User\Enum\UserType;
use App\Domain\User\Enum\AccountStatus;
use App\Application\Bus\EventBusInterface;
use App\Domain\User\Event\ToggleUserAccountEvent;
use App\Domain\User\Exception\DomainUserNotFoundException;
use App\Domain\User\Service\Registry\UserManagerRegistryInterface;
use App\Domain\User\Service\Security\Generator\PasswordGeneratorInterface;

/**
 * Use case pour l'activation ou la désactivation d'un compte utilisateur.
 * 
 * Responsabilités :
 * - Rechercher l'utilisateur par son username/email
 * - Générer un nouveau mot de passe lors de l'activation
 * - Mettre à jour le statut du compte
 * - Déclencher un événement de domaine pour notifications
 * 
 * Note : Le mot de passe n'est généré que lors de l'ACTIVATION,
 * pas lors de la désactivation.
 *
 * @author AGBOKOUDJO Franck <internationaleswebservices@gmail.com>
 * @package App\Application\UseCase\CommandHandler\User
 */
final class ToggleUserAccountUseCase
{
    /**
     * Longueur du mot de passe temporaire généré à l'activation.
     */
    private const TEMPORARY_PASSWORD_LENGTH = 20;

    public function __construct(
        private readonly UserManagerRegistryInterface $userManagerRegistry,
        private readonly EventBusInterface $eventBus,
        private readonly PasswordGeneratorInterface $passwordGenerator
    ) {}

    /**
     * Active ou désactive un compte utilisateur.
     * 
     * Lors de l'ACTIVATION :
     * - Génère un nouveau mot de passe temporaire fort
     * - L'utilisateur recevra ce mot de passe par email
     * - Il devra le changer à sa première connexion
     * 
     * Lors de la DÉSACTIVATION :
     * - Le compte est simplement désactivé
     * - Le mot de passe existant est conservé
     *
     * @param UserType $userType Le type d'utilisateur (ADMIN, MEMBER, etc.)
     * @param string $usernameOrEmail L'identifiant de l'utilisateur (username ou email)
     * @param AccountStatus $status Le nouveau statut du compte
     * 
     * @return void
     * 
     * @throws DomainUserNotFoundException Si l'utilisateur n'existe pas
     * @throws InvalidArgumentException Si les paramètres sont invalides
     * @throws \RuntimeException Si le manager approprié n'est pas trouvé
     */
    public function handle(
        UserType $userType,
        string $usernameOrEmail,
        AccountStatus $status = AccountStatus::ACTIVE
    ): void
    {
        // 1. Validation des entrées
        $this->validateInput($usernameOrEmail);

        // 2. Récupération du gestionnaire approprié
        $userManager = $this->userManagerRegistry->getByUserType($userType);

        // 3. Recherche de l'utilisateur
        $user = $userManager->findUserByUsernameOrEmail($usernameOrEmail);

        if ($user === null) {
            throw new DomainUserNotFoundException($usernameOrEmail);
        }

        // 4. Vérification si le statut change réellement
        if ($user->isEnabled() === $status->toBool()) {
            // Pas de changement nécessaire
            return;
        }

        // 5. Application du changement de statut
        $plainPassword = null;

        if ($status === AccountStatus::ACTIVE) {
            // Activation : générer un nouveau mot de passe temporaire
            $plainPassword = $this->generateTemporaryPassword();
            $user->setPlainPassword($plainPassword);
        }

        $user->setEnabled($status->toBool());

        // 6. Persistance des changements
        $userManager->save($user);

        // 7. Déclenchement de l'événement de domaine
        $event = new ToggleUserAccountEvent($user, $plainPassword);
        $this->eventBus->dispatch($event);
    }

    /**
     * Valide les données d'entrée.
     *
     * @param string $usernameOrEmail L'identifiant à valider
     * 
     * @throws InvalidArgumentException Si l'identifiant est vide
     */
    private function validateInput(string $usernameOrEmail): void
    {
        if (trim($usernameOrEmail) === '') {
            throw new \InvalidArgumentException(
                'L\'identifiant utilisateur (username ou email) ne peut pas être vide.'
            );
        }
    }

    /**
     * Génère un mot de passe temporaire fort.
     * 
     * Le mot de passe généré contient :
     * - Lettres majuscules et minuscules
     * - Chiffres
     * - Caractères spéciaux (optionnel selon configuration)
     *
     * @return string Le mot de passe en clair
     * 
     * @throws \RuntimeException Si la génération échoue
     */
    private function generateTemporaryPassword(): string
    {
        try {
            return $this->passwordGenerator->generate(
                self::TEMPORARY_PASSWORD_LENGTH,
                true,  // Inclure symboles pour plus de sécurité
                true   // Exclure caractères ambigus (0/O, 1/l/I)
            );
        } catch (\Exception $e) {
            throw new \RuntimeException(
                'Impossible de générer un mot de passe sécurisé.',
                0,
                $e
            );
        }
    }
}
