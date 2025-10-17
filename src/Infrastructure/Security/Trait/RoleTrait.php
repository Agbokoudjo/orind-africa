<?php

declare(strict_types=1);

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Infrastructure\Security\Trait;

use Sonata\AdminBundle\Admin\AdminInterface;

/**
 * @author AGBOKOUDJO Franck <internationaleswebservices@gmail.com>
 * @package <https://github.com/Agbokoudjo/>
 */
trait RoleTrait{

    public function getBaseRole(AdminInterface $admin): string
    {
        return \sprintf('ROLE_%s_%%s', str_replace('.', '_', strtoupper($admin->getCode())));
    }
}