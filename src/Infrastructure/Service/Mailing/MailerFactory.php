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

use Twig\Environment;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Message;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Crypto\DkimSigner;
use App\Infrastructure\QueueHandler\EnqueueMethod;


/**
 * @author AGBOKOUDJO Franck <internationaleswebservices@gmail.com>
 * @package <https://github.com/Agbokoudjo/>
 */
abstract class MailerFactory implements MailerFactoryInterface
{
    public function __construct(
        private readonly  Environment $twig,
        private readonly  EnqueueMethod $enqueue,
        private readonly  MailerInterface $mailer,
        private readonly  ?string $dkimKey = null,
        private readonly array $fromAddresses // injecté depuis services.yaml
    ) {}

    protected function createEmailInternal(
        string $type,
        string $template,
        array $data = []
    ): Email {
        if (!isset($this->fromAddresses[$type])) {
            throw new \RuntimeException(\sprintf("Unknown mailer type %s",$type));
        }

        $fromConfig = $this->fromAddresses[$type];

        $this->twig->addGlobal('format', 'html');
        $html = $this->twig->render($template, array_merge($data, ['layout' => 'mails/base.html.twig']));
        $this->twig->addGlobal('format', 'text');
        $text = $this->twig->render($template, array_merge($data, ['layout' => 'mails/base.text.twig']));

        return (new Email())
            ->from(new Address($fromConfig['address'], $fromConfig['name']))
            ->html($html)
            ->text($text);
    }
    public function send(Email $email): void
    {
        $this->enqueue->handler(self::class, 'sendNow', [$email]);
    }

    public function sendNow(Email $email): void
    {

        if ($this->dkimKey) {
            $dkimSigner = new DkimSigner("file://{$this->dkimKey}", 'grafikart.fr', 'default');
            $message = new Message($email->getPreparedHeaders(), $email->getBody());
            $email = $dkimSigner->sign($message, []);
        }
        $this->mailer->send($email);
    }
    abstract public function createEmail(string $template, array $data = []): Email;
}
