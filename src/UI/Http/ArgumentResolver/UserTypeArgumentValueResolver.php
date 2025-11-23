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
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

/**
 * Résout automatiquement les paramètres UserType depuis les routes.
 * Décode le Base64 et crée l'enum correspondant.
 * @author AGBOKOUDJO Franck <internationaleswebservices@gmail.com>
 * @package <https://github.com/Agbokoudjo/>
 */
final class  UserTypeArgumentValueResolver implements ValueResolverInterface
{
    public function resolve(Request $request, ArgumentMetadata $argument): iterable{

        // Vérifier si le type attendu est UserType
        if ($argument->getType() !== UserType::class) {
            return [];
        }
          // Récupérer le paramètre de route encodé en Base64
        $parameterName = $argument->getName(); 
        $encodedValue = $request->attributes->get($parameterName);

        if ($encodedValue === null) {
            return [];
        }

        // Valider que c'est une chaîne
        if (!is_string($encodedValue)) {
            throw new BadRequestHttpException(
                sprintf(
                    'Le paramètre "%s" doit être une chaîne, %s reçu.',
                    $parameterName,
                    gettype($encodedValue)
                )
            );
        }

        // Décoder le Base64
        $decodedValue = base64_decode($encodedValue, strict: true);
        if ($decodedValue === false) {
            throw new BadRequestHttpException(
                sprintf(
                    '🔴 Le paramètre "%s" n\'est pas encodé correctement en Base64 : "%s"',
                    $parameterName,
                    $encodedValue
                )
            );
        }
       
        if (empty($decodedValue)) {
            throw new BadRequestHttpException(
                sprintf('🔴 Le paramètre "%s" décodé est vide.', $parameterName)
            );
        }

        // Créer l'enum à partir de la valeur décodée
        try {
            $userType = UserType::from($decodedValue);
            return [$userType];
        } catch (\ValueError $e) {
            // La valeur décodée ne correspond à aucun cas de l'enum
            throw new BadRequestHttpException(
                sprintf(
                    '🔴 Le type utilisateur "%s" n\'existe pas. Valeurs valides : %s',
                    $decodedValue,
                    implode(', ', array_map(
                        fn(UserType $type) => $type->value,
                        UserType::cases()
                    ))
                ),
                $e,
                0
            );
        }
    }
}
