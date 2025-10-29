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

use Symfony\Component\Messenger\Stamp\DelayStamp;
use Symfony\Component\Messenger\MessageBusInterface;
use App\Application\Queue\Message\ServiceMethodMessage;

/**
 *  Permet de demande l'exécution d'une méthode d'un service de manière asynchrone.
 * @author AGBOKOUDJO Franck <internationaleswebservices@gmail.com>
 * @package <https://github.com/Agbokoudjo/>
 */

namespace App\Infrastructure\QueueHandler;

use App\Application\Queue\AsyncMethodDispatcherInterface;
use Symfony\Component\Messenger\Stamp\DelayStamp;
use Symfony\Component\Messenger\MessageBusInterface;
use App\Application\Queue\Message\ServiceMethodMessage;

/**
 * Classe ServiceMethodMessage
 *
 * Transporte les informations nécessaires pour exécuter de manière asynchrone 
 * une méthode d’un service donné. Elle contient le nom complet du service, 
 * la méthode à appeler et les paramètres à lui passer. 
 * Ce message est utilisé par le composant Messenger pour différer ou déléguer 
 * l’exécution d’une tâche dans une file de traitement.
 * 
 * @author AGBOKOUDJO Franck <internationaleswebservices@gmail.com>
 * @package <https://github.com/Agbokoudjo/>
 */
final class AsyncMethodDispatcher implements AsyncMethodDispatcherInterface
{
    public function __construct(private readonly MessageBusInterface $bus) {}

    public function dispatch(string $service, string $method, array $params = [], ?\DateTimeInterface $date = null): void
    {
        $stamps = [];
        if ($date) {
            $delay = 1000 * ($date->getTimestamp() - time());
            if ($delay > 0) {
                $stamps[] = new DelayStamp($delay);
            }
        }

        $this->bus->dispatch(new ServiceMethodMessage($service, $method, $params), $stamps);
    }
}