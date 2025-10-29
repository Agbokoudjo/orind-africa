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

namespace App\UI\Http\ArgumentResolver;

use App\Domain\User\Enum\UserType;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

/**
 * Résout automatiquement les paramètres UserType depuis les routes.
 * @author AGBOKOUDJO Franck <internationaleswebservices@gmail.com>
 * @package <https://github.com/Agbokoudjo/>
 */
#[AutoconfigureTag(name: 'controller.argument_value_resolver',attribute:['priority'=>150])]
final class  UserTypeArgumentValueResolver implements ValueResolverInterface
{
    public function resolve(Request $request, ArgumentMetadata $argument): iterable{

        // Vérifier si le type attendu est UserType
        if ($argument->getType() !== UserType::class) {
            return [];
        }
        // 2. Récupérer la valeur encodée depuis les attributs de la route (user_type)
        $encodedValue = $request->attributes->get($argument->getName());

        if ($encodedValue === null) {
            return [];
        }
        // --- NOUVELLE LOGIQUE : DECODAGE BASE64 ---

        // 3. Décoder la valeur Base64
        // On suppose que la valeur est valide, sinon le décodage peut produire des caractères
        // inattendus. Le filtre base64_decode ne lève pas d'exception sur une mauvaise chaîne.
        $value = base64_decode($encodedValue, true);

        // Si le décodage échoue ou si la chaîne est vide après décodage
        if ($value === false || $value === '') {
            throw new NotFoundHttpException(
                sprintf('Le type utilisateur encodé est invalide ou vide : "%s".', $encodedValue)
            );
        }
        // --- FIN NOUVELLE LOGIQUE ---
        try {
            // 4. Tenter de créer l'enum à partir de la valeur décodée
            return [UserType::from($value)];
        } catch (\ValueError $e) {
            // 5. Valeur invalide (décoder, mais ne correspond à aucun cas d'Enum)
            throw new NotFoundHttpException(
                sprintf(
                    'Type d\'utilisateur non reconnu : "%s". Valeurs valides : %s',
                    $value,
                    implode(', ', array_map(fn(UserType $t) => $t->value, UserType::cases()))
                ),
                $e 
            );
        }
    }
}
