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

namespace App\Infrastructure\QueueHandler;

use Psr\Container\ContainerInterface;
use App\Application\UseCase\User\LoggerLoginUseCase;
use App\Application\Queue\Message\ServiceMethodMessage;
use Symfony\Contracts\Service\ServiceSubscriberInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use App\Application\UseCase\User\UpdateUserProfileCommandHandler;
use App\Application\QueueHandler\ServiceMethodMessageHandlerInterface;
use App\Application\UseCase\CommandHandler\ActivityLogCommandHandler;

/**
 * Classe ServiceMethodMessageHandler
 *
 * Gère l’exécution asynchrone des méthodes de service envoyées sous forme de message.
 * Cette classe agit comme un handler du composant Messenger de Symfony : elle reçoit
 * un objet {@see ServiceMethodMessage}, récupère dynamiquement le service concerné
 * depuis le conteneur, puis invoque la méthode indiquée avec les paramètres fournis.
 *
 * Elle implémente {@see ServiceSubscriberInterface} afin de déclarer explicitement 
 * les services qu’elle est autorisée à consommer, garantissant ainsi une meilleure 
 * autoconfiguration et un couplage faible.
 *
 * En résumé, cette classe permet d’exécuter à distance ou de manière différée 
 * des appels de méthodes sur des services applicatifs via la file de messages.
 * 
 * @author AGBOKOUDJO Franck <internationaleswebservices@gmail.com>
 * @package <https://github.com/Agbokoudjo/>
 */
#[AsMessageHandler(fromTransport: "async_app_transport",handles:ServiceMethodMessage::class)]
final class ServiceMethodMessageHandler implements ServiceSubscriberInterface,ServiceMethodMessageHandlerInterface
{
    public function __construct(private readonly ContainerInterface $container) {}

    public static function getSubscribedServices(): array
    {
        return [
            LoggerLoginUseCase::class,
            UpdateUserProfileCommandHandler::class,
            ActivityLogCommandHandler::class
        ];
    }

    public function __invoke(ServiceMethodMessage $message): void
    {
        $service = $this->container->get($message->getServiceName());
        $method = $message->getMethod();
        $params = $message->getParams();

        $service->$method(...$params);
    }
}
