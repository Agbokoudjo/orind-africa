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

namespace App\Infrastructure\Listener;

use App\Application\Queue\EnqueueMethodInterface;
use App\Application\UseCase\LoggerLoginUseCase;
use App\Domain\User\BaseUserInterface;
use Symfony\Component\Security\Http\SecurityEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

/**
 * @internal
 * @author AGBOKOUDJO Franck <internationaleswebservices@gmail.com>
 * @package <https://github.com/Agbokoudjo/>
 */
final class LastLoginListener implements EventSubscriberInterface
{
    public function __construct(private  EnqueueMethodInterface $enqueue) {}

    public static function getSubscribedEvents(): array
    {
        return [
            SecurityEvents::INTERACTIVE_LOGIN => 'onSecurityInteractiveLogin',
        ];
    }

    public function onSecurityInteractiveLogin(InteractiveLoginEvent $event): void
    {
        $user = $event->getAuthenticationToken()->getUser();

        if (!$user instanceof BaseUserInterface) {
            return;
        }

        if ($_ENV['APP_EXECUTE_LISTENER'] === "false") {
            return;
        }
        
        $this->enqueue->handler(LoggerLoginUseCase::class, "recordLogin",[$user, $event->getRequest()->getClientIp()]);
    }
}
