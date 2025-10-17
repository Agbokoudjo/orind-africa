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

namespace App\Domain\Manager;

interface ManagerCRUDInterface
{
    /**
     * Create an empty Entity instance.
     *
     * @phpstan-return T
     */
    public function create(): object;

    /**
     * Save an Entity.
     *
     * @param object $entity   The Entity to save
     * @param bool   $andFlush Flush the EntityManager after saving the object?
     *
     * @phpstan-param T $entity
     */
    public function save(object $entity, bool $andFlush = true): void;

    /**
     * Delete an Entity.
     *
     * @param object $entity   The Entity to delete
     * @param bool   $andFlush Flush the EntityManager after deleting the object?
     *
     * @phpstan-param T $entity
     */
    public function delete(object $entity, bool $andFlush = true): void;
}
