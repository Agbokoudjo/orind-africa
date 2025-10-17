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

namespace App\Domain\User\Service;

use App\Domain\User\BaseUserInterface;
use App\Domain\User\Manager\UserManagerInterface;
use App\Domain\User\Manager\UserManagerRegistryInterface;
use App\Domain\User\Service\UserAccountListenerInterface;
use App\Domain\User\Service\CanonicalFieldsUpdaterInterface;

/**
 * @author AGBOKOUDJO Franck <internationaleswebservices@gmail.com>
 * @package <https://github.com/Agbokoudjo/>
 */
final class UserAccountListener implements UserAccountListenerInterface{
    public function __construct(
    private UserManagerRegistryInterface $user_manager_registry,
    private CanonicalFieldsUpdaterInterface $canonicalFields){ }

    public function prePersist(BaseUserInterface $entity): void
    {
        $this->getUserManager($entity)->updatePassword($entity);
        $this->canonicalFields->updateCanonicalFields($entity);
        $slug_hash=md5(\sprintf('%s_%d',$entity->getUsernameCanonical(),time()));
        $entity->setSlug(sha1($slug_hash));
        $entity->prePersist();
    }

    public function preUpdate(BaseUserInterface $entity): void
    {
        $this->getUserManager($entity)->updatePassword($entity);
        $this->canonicalFields->updateCanonicalFields($entity);
        $slug_hash = md5(\sprintf('%s_%d', $entity->getUsernameCanonical(), time()));
        $entity->setSlug(sha1($slug_hash));
        $entity->preUpdate();
    }
    
    public function getUserManager(BaseUserInterface $entity):UserManagerInterface{
        return $this->user_manager_registry->get($entity);
    }
}
