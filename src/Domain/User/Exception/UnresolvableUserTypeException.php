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

namespace App\Domain\User\Exception;

use DomainException;
use App\Domain\User\Model\BaseUserInterface;

/**
 * Exception levée lorsque le type d'un utilisateur ne peut pas être déterminé.
 * 
 * Indique que l'instance utilisateur n'implémente aucune des interfaces
 * reconnues (AdminUserInterface, MemberUserInterface, etc.) ou qu'une
 * nouvelle implémentation n'a pas encore été ajoutée au resolver.
 *
 * @author AGBOKOUDJO Franck <internationaleswebservices@gmail.com>
 * @package App\Domain\User\Exception
 */
final class UnresolvableUserTypeException extends DomainException
{
    /**
     * Constructeur de l'exception.
     *
     * @param string $message Message d'erreur descriptif
     * @param int $code Code d'erreur (par défaut 0)
     * @param \Throwable|null $previous Exception précédente pour le chaînage
     */
    public function __construct(
        string $message = 'Impossible de déterminer le type de cet utilisateur.',
        int $code = 0,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }

    /**
     * Crée une exception pour un utilisateur non résolvable.
     * 
     * Génère un message d'erreur incluant la classe de l'utilisateur
     * pour faciliter le débogage.
     *
     * @param BaseUserInterface $user L'utilisateur dont le type n'a pas pu être résolu
     * 
     * @return self L'exception configurée
     * 
     * @example
     * throw UnresolvableUserTypeException::forUser($unknownUser);
     */
    public static function forUser(BaseUserInterface $user): self
    {
        return new self(
            sprintf(
                'Impossible de déterminer le type pour l\'utilisateur de classe "%s". '
                    . 'Assurez-vous que cette classe implémente l\'une des interfaces : '
                    . 'AdminUserInterface, MemberUserInterface, ClientUserInterface ou SimpleUserInterface.',
                $user::class
            )
        );
    }

    /**
     * Crée une exception pour une implémentation non supportée.
     * 
     * Utilisé quand une nouvelle classe d'utilisateur existe mais
     * n'a pas encore été ajoutée au resolver.
     *
     * @param string $className Le nom de la classe non supportée
     * 
     * @return self L'exception configurée
     * 
     * @example
     * throw UnresolvableUserTypeException::unsupportedImplementation(PartnerUser::class);
     */
    public static function unsupportedImplementation(string $className): self
    {
        return new self(
            sprintf(
                'L\'implémentation utilisateur "%s" n\'est pas encore supportée par le resolver. '
                    . 'Veuillez ajouter le mapping correspondant dans App\Domain\User\Service\Resolver\UserTypeResolver::resolveFromUser().',
                $className
            )
        );
    }
}
