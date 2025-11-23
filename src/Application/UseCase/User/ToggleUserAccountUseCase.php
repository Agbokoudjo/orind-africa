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
use App\Application\Service\GenerateTemporaryPasswordService ;

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
    public function __construct(
        private readonly UserManagerRegistryInterface $userManagerRegistry,
        private readonly EventBusInterface $eventBus,
        private readonly GenerateTemporaryPasswordService $generateTemporaryPassword
    ) {}

    /**
     * Active ou désactive un compte utilisateur.
     * 
     * Logique d'activation :
     * - Si c'est la PREMIÈRE ACTIVATION (pas de mot de passe) :
     *   → Génère un mot de passe temporaire fort
     *   → L'utilisateur le recevra par email
     *   → Il devra le changer à sa première connexion
     * 
     * - Si c'est une RÉACTIVATION (mot de passe existant) :
     *   → Le mot de passe existant est conservé
     *   → L'utilisateur peut se connecter directement
     * 
     * Logique de désactivation :
     * - Le compte est désactivé
     * - Le mot de passe existant est conservé
     *
     * @param UserType $userType Le type d'utilisateur (ADMIN, MEMBER, etc.)
     * @param string $usernameOrEmail L'identifiant de l'utilisateur (username ou email)
     * @param AccountStatus $status Le nouveau statut du compte
     * 
     * @return void
     * 
     * @throws InvalidArgumentException Si les paramètres sont invalides
     * @throws DomainUserNotFoundException Si l'utilisateur n'existe pas
     * @throws RuntimeException Si une erreur système se produit
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
        $plainPasswordTemporary = null;
        /**
         * Gère la logique spécifique au mot de passe lors de l'activation (l'entité est passée à 'enabled').
         * * Cette logique assure l'intégrité des données et la sécurité concernant le mot de passe :
         * * 1. Nouvel Utilisateur (Première Activation) :
         * - SI l'utilisateur n'a PAS de mot de passe haché (c'est-à-dire que c'est la première activation),
         * un nouveau mot de passe sécurisé DOIT être généré et enregistré.
         * * 2. Utilisateur Existant :
         * - SI l'utilisateur possède DÉJÀ un mot de passe haché, AUCUN nouveau mot de passe n'est généré.
         * Le mot de passe existant DOIT être conservé pour maintenir l'accès de l'utilisateur.
         * */
        if ($status === AccountStatus::ACTIVE && !$user->hasPassword()) {
            // Activation : générer un nouveau mot de passe temporaire
            $plainPasswordTemporary = $this->generateTemporaryPassword->generateTemporaryPassword();
            $user->setPlainPassword($plainPasswordTemporary);
        }

        // Mettre à jour le statut du compte
        $user->setEnabled($status->toBool());
        $userManager->updatePassword($user);
        //  Persistance des changements
        $userManager->save($user); 

        //  Déclenchement de l'événement de domaine
        $event = new ToggleUserAccountEvent($user, $plainPasswordTemporary);
        $this->eventBus->dispatch($event);
    }

    
    /**
     * Valide les données d'entrée.
     *
     * @throws InvalidArgumentException Si l'identifiant est vide ou invalide
     */
    private function validateInput(string $usernameOrEmail): void
    {
        if (trim($usernameOrEmail) === '') {
            throw new \InvalidArgumentException(
                'L\'identifiant utilisateur (username ou email) ne peut pas être vide.'
            );
        }

        if (strlen($usernameOrEmail) > 255) {
            throw new \InvalidArgumentException(
                'L\'identifiant utilisateur ne peut pas dépasser 255 caractères.'
            );
        }
    }
}
