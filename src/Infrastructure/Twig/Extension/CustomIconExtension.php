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

namespace App\Infrastructure\Twig\Extension;

use Twig\TwigFilter;
use Twig\Extension\AbstractExtension;
use App\Infrastructure\Twig\CustomIconRuntime;

final class CustomIconExtension extends AbstractExtension
{

    public function getFilters(): array
    {
        return [
            new TwigFilter('custom_parse_icon', [CustomIconRuntime::class, 'parseIcon'], ['is_safe' => ['html']]),
        ];
    }
}
