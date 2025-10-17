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

use App\Application\Queue\EnqueueMethodInterface;
use Symfony\Component\Messenger\Stamp\DelayStamp;
use Symfony\Component\Messenger\MessageBusInterface;
use App\Application\Queue\Message\ServiceMethodMessage;

/**
 *  Permet de demande l'exécution d'une méthode d'un service de manière asynchrone.
 * @author AGBOKOUDJO Franck <internationaleswebservices@gmail.com>
 * @package <https://github.com/Agbokoudjo/>
 */
final class EnqueueMethod implements EnqueueMethodInterface
{
    public function __construct(private readonly MessageBusInterface $bus) {}

    public function handler(string $service, string $method, array $params = [], ?\DateTimeInterface $date = null): void
    {
        $stamps = [];
        // Le service doit être appelé avec un délai
        if (null !== $date) {
            $delay = 1000 * ($date->getTimestamp() - time());
            if ($delay > 0) {
                $stamps[] = new DelayStamp($delay);
            }
        }
        $this->bus->dispatch(new ServiceMethodMessage($service, $method, $params), $stamps);
    }
}
