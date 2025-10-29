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

use App\Domain\User\Message\UpdateUserProfileCommand;
use App\Domain\User\Service\CanonicalFieldsUpdaterInterface;
use App\Domain\User\Service\Registry\UserManagerRegistryInterface;

/**
 * @author AGBOKOUDJO Franck <internationaleswebservices@gmail.com>
 * @package <https://github.com/Agbokoudjo/>
 */
final readonly class UpdateUserProfileCommandHandler
{
    public function __construct(
        private UserManagerRegistryInterface $managerRegistry,
        private CanonicalFieldsUpdaterInterface $canonicalFields
    ) {}

    public function handler(UpdateUserProfileCommand $command): void
    {
        // 1. Récupérer le Manager approprié
        $manager = $this->managerRegistry->getByUserType($command->getUserType());

        // 2. Récupérer l'Entité (méthode à ajouter au Manager)
        $user = $manager->find($command->getUserId());

        if (null === $user) {
          
            return;
        }

        // 3. Exécution de la logique déléguée :

        // A. Mise à jour des champs canoniques (et l'e-mail du User)
        $this->canonicalFields->updateCanonicalFields($user);

        // B. Mise à jour du mot de passe (nécessaire si c'est une création asynchrone)
        $manager->updatePassword($user);

        // C. Génération du Slug
        
        $slug_hash = md5(\sprintf('%s_%d', $user->getUsernameCanonical(), time()));
        $user->setSlug(sha1($slug_hash));

        // 4. Sauvegarde des changements (doit être gérée par le Manager)
        $manager->save($user);
    }
}
