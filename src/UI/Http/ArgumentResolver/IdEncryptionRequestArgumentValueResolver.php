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

use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpFoundation\Request;
use App\Infrastructure\Security\Encryption\IdEncryptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

/**
 * Service pour chiffrer et déchiffrer les IDs des entités
 * 
 * Utilise Base64 URL-SAFE pour éviter les conflits avec les routes
 * @author AGBOKOUDJO Franck <internationaleswebservices@gmail.com>
 * @package <https://github.com/Agbokoudjo/>
 */
final class  IdEncryptionRequestArgumentValueResolver implements ValueResolverInterface
{
    public function __construct(
        private readonly IdEncryptionInterface $encryptionService
    ) {}

     public function resolve(Request $request, ArgumentMetadata $argument): iterable{

        if($argument->getType() !==Request::class){
            return [] ;
        }

        if (!$request->attributes->has('id')) {
            return [];
        }

        $encodedId = $request->attributes->get('id');

        if (is_numeric($encodedId) || empty($encodedId) || !\is_string($encodedId)) {
            return [];
        }

        try {
            // Décrypter l'ID
            $decodedId = $this->encryptionService->decryptId($encodedId);

            // Vérifier que le résultat est un entier valide
            if (!is_numeric($decodedId) || (int)$decodedId <= 0) {
                throw new \Exception("ID décodé invalide.");
            }
            
            $request->attributes->set('id', (string)$decodedId);
            return [$request];
        } catch (\Exception $e) {
             // Pour les URLs avec un ID malformé, on lance un 404 pour empêcher toute fuite d'information.
              $request->attributes->set('id', null); 
             throw new NotFoundHttpException(\sprintf("Ressource introuvable : Identifiant invalide."));
        }
     }
}
