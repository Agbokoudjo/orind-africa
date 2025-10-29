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

namespace App\Infrastructure\Bus;

use App\Application\Bus\EventBusInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * Adaptateur Symfony pour le bus d'événements.
 * 
 * Implémente EventBusInterface en déléguant à l'EventDispatcher de Symfony.
 * Cette classe fait le pont entre notre abstraction métier et Symfony.
 *
 * @package App\Infrastructure\Bus
 * @author AGBOKOUDJO Franck <internationaleswebservices@gmail.com>
 */
final class SymfonyEventBusAdapter implements EventBusInterface
{
    public function __construct(
        private readonly EventDispatcherInterface $eventDispatcher
    ) {}

    public function dispatch(object $event,?string $eventName=null): object
    {
        return $this->eventDispatcher->dispatch($event, $eventName);
    }
}