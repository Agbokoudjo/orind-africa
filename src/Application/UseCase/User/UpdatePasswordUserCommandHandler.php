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

use App\Application\Bus\EventBusInterface;
use App\Domain\User\Event\UpdatePasswordUserEvent;
use App\Domain\User\Message\UpdatePasswordUserCommand;
use App\Domain\User\Service\Registry\UserManagerRegistryInterface;

/**
 * @author AGBOKOUDJO Franck <internationaleswebservices@gmail.com>
 * @package <https://github.com/Agbokoudjo/>
 */
final readonly class UpdatePasswordUserCommandHandler
{
    public function __construct(
        private UserManagerRegistryInterface $managerRegistry,
        private EventBusInterface $eventDispatcher
    ) {}

    public function handle(UpdatePasswordUserCommand $commandPassword): void
    {
        $manager = $this->managerRegistry->getByUserType($commandPassword->getUserType());

        $user = $manager->find($commandPassword->getUserId());

        if (null === $user) {
            return;
        }

        $user->setPlainPassword($commandPassword->getGeneratePlainPassword());
        $manager->updatePassword($user);
        $user->preUpdate();
        $manager->save($user);

        $this->eventDispatcher->dispatch(
            new UpdatePasswordUserEvent($user,$commandPassword->getGeneratePlainPassword())
            ) ;
    }
}
