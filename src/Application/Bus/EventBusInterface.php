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

namespace App\Application\Bus;

/**
 * Interface d'abstraction du bus d'événements.
 *
 * Permet de dispatcher des événements de domaine sans dépendre
 * d'un framework spécifique (Symfony, Laravel, etc.).
 * 
 * L'implémentation concrète se trouve dans la couche Infrastructure.
 *
 * @package App\Application\Bus
 * @author AGBOKOUDJO Franck <internationaleswebservices@gmail.com>
 */
interface EventBusInterface
{
    /**
     * Dispatche un événement sur le bus.
     *
     * @param object $event L'événement à dispatcher
     * 
     * @return object
     */
    public function dispatch(object $event,?string $eventName=null): object;
}
