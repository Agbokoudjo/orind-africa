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

namespace App\Domain\User\Service\Resolver;

use App\Domain\User\Enum\UserType;
use App\Domain\User\Model\BaseUserInterface;
use App\Domain\User\Model\AdminUserInterface;
use App\Domain\User\Model\ClientUserInterface;
use App\Domain\User\Model\MemberUserInterface;
use App\Domain\User\Model\SimpleUserInterface;
use App\Domain\User\Exception\UnresolvableUserTypeException;

/**
 * Résolveur de type d'utilisateur basé sur l'interface implémentée.
 *
 * Détermine le type d'un utilisateur (ADMIN, MEMBER, CLIENT, SIMPLE)
 * en analysant les interfaces qu'il implémente.
 *
 * Utile pour :
 * - Résolution de routes dynamiques
 * - Génération de liens de vérification
 * - Logique métier conditionnelle
 *
 * @author AGBOKOUDJO Franck <internationaleswebservices@gmail.com>
 * @package App\Domain\User
 */
final readonly class UserTypeResolver{

    /**
    * Constructeur privé pour empêcher l'instanciation.
    *
    * Cette classe est purement utilitaire et n'a pas d'état.
    */
    private function __construct(){ }

    /**
    * Résout le type d'un utilisateur à partir de son instance.
    *
    * Analyse les interfaces implémentées pour déterminer le type :
    * - AdminUserInterface → UserType::ADMIN
    * - MemberUserInterface → UserType::MEMBER
    * - ClientUserInterface → UserType::CLIENT
    * - SimpleUserInterface (ou défaut) → UserType::SIMPLE
    *
    * @param BaseUserInterface $user L'instance de l'utilisateur à analyser
    *
    * @return UserType Le type d'utilisateur correspondant
    *
    * @throws UnresolvableUserTypeException Si le type ne peut pas être déterminé
    * (implémentation non supportée ou incomplète)
    *
    * @example
    * $admin = new AdminUser();
    * $type = UserTypeResolver::resolveFromUser($admin); // UserType::ADMIN
    */
    public static function resolveFromUser(BaseUserInterface $user): UserType
    {
        return match (true) {
            $user instanceof AdminUserInterface => UserType::ADMIN,
            $user instanceof MemberUserInterface => UserType::MEMBER,
            $user instanceof ClientUserInterface => UserType::CLIENT,
            $user instanceof SimpleUserInterface => UserType::SIMPLE,

            // ❌ Aucune interface reconnue
            default => throw UnresolvableUserTypeException::forUser($user),
        };
    }

    /**
    * Vérifie si un utilisateur peut être résolu en type.
    *
    * Utile pour tester si une instance utilisateur implémente
    * une interface reconnue avant de tenter la résolution.
    *
    * @param BaseUserInterface $user L'utilisateur à vérifier
    *
    * @return bool True si le type peut être résolu, false sinon
    *
    * @example
    * if (UserTypeResolver::canResolve($user)) {
    * $type = UserTypeResolver::resolveFromUser($user);
    * }
    */
    public static function canResolve(BaseUserInterface $user): bool
    {
        return $user instanceof AdminUserInterface
        || $user instanceof MemberUserInterface
        || $user instanceof ClientUserInterface
        || $user instanceof SimpleUserInterface;
    }

    /**
    * Résout le type avec une valeur par défaut si la résolution échoue.
    *
    * Alternative sûre à resolveFromUser() qui ne lève pas d'exception.
    *
    * @param BaseUserInterface $user L'utilisateur à analyser
    * @param UserType $default Le type par défaut si résolution impossible
    *
    * @return UserType Le type résolu ou la valeur par défaut
    *
    * @example
    * // Retourne SIMPLE si le type ne peut pas être déterminé
    * $type = UserTypeResolver::resolveOrDefault($user, UserType::SIMPLE);
    */
    public static function resolveOrDefault(BaseUserInterface $user,UserType $default = UserType::SIMPLE): UserType {
        try {
            return self::resolveFromUser($user);
            
        } catch (UnresolvableUserTypeException) {
            return $default;
        }
    }
}