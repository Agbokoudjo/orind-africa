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

namespace App\Infrastructure\Translation;

use App\Application\Service\TranslatorInterface;
use App\Infrastructure\Locale\SymfonyLocaleProvider;
use Symfony\Contracts\Translation\TranslatorInterface as SymfonyTranslator;

/**
 * @author AGBOKOUDJO Franck <internationaleswebservices@gmail.com>
 * @package <https://github.com/Agbokoudjo/>
 */
final class SymfonyTranslatorAdapter implements TranslatorInterface
{
    public function __construct(
        private SymfonyTranslator $translator,
        private SymfonyLocaleProvider $localeProvider
    ) {}

    public function trans(string $id, array $parameters = [], string $domain = 'messages'): string
    {
        return $this->translator->trans($id, $parameters, $domain, $this->localeProvider->getLocale());
    }
}
