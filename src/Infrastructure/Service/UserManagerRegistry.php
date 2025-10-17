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

namespace App\Infrastructure\Service;

use App\Domain\User\Manager\AbstractUserManagerRegistry;
use App\Infrastructure\Persistance\CustomUserManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

/**
 * @author AGBOKOUDJO Franck <internationaleswebservices@gmail.com>
 * @package <https://github.com/Agbokoudjo/>
 */
final class UserManagerRegistry extends AbstractUserManagerRegistry
{
    /**
     * @param iterable<CustomUserManagerInterface> $managers
     */
    public function __construct(
        #[AutowireIterator('app.user.manager')]
        iterable $managers)
    {
        parent::__construct($managers);
    }
}
