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

namespace App\Infrastructure\Service\Mailing;

use Symfony\Component\Mime\Email;
use App\Infrastructure\Service\Mailing\MailerFactory;
use Twig\Environment;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Message;
use Symfony\Component\Mailer\MailerInterface;
use App\Infrastructure\QueueHandler\EnqueueMethod;

/**
 * @author AGBOKOUDJO Franck <internationaleswebservices@gmail.com>
 * @package <https://github.com/Agbokoudjo/>
 */
final class SystemMailer extends MailerFactory 
{
    public function __construct(
        private readonly  Environment $twig,
        private readonly  EnqueueMethod $enqueue,
        private readonly  MailerInterface $mailer,
        private readonly  ?string $dkimKey = null,
        private readonly array $fromAddresses // injecté depuis services.yaml
    ) {
        parent::__construct($twig,$enqueue,$mailer,$dkimKey,$fromAddresses);
    }
    public function createEmail(string $template,array $data = []): Email {
       return $this->createEmailInternal('system',$template,$data);
    }
    
}
