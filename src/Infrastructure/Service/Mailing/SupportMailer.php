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

use RuntimeException;
use Psr\Log\LoggerInterface;
use App\Infrastructure\Service\Mailing\MailerFactoryInterface;
use App\Infrastructure\Service\Mailing\SupportMailerInterface;

final class SupportMailer implements SupportMailerInterface
{
    private const CONFIG_TYPE = 'support';

    public function __construct(
        private readonly MailerFactoryInterface $mailerFactory,
        private readonly ?LoggerInterface $logger = null) {}

    public function sendManager(
        string $senderEmail,
        string $recipientEmail,
        string $subject,
        string $htmlTemplate,
        ?array $context = null,
        ?string $replyToEmail = null
    ): void {
       
        try {
            $supportEmail = $this->mailerFactory->createTemplateEmail(
                $senderEmail,
                $this->mailerFactory->fromConfig('support')['name'],
                $recipientEmail,
                $subject,
                $htmlTemplate,
                $context ?? []
            );

            $supportEmail->replyTo($replyToEmail ?? $senderEmail);

            $supportEmail->getHeaders()->addTextHeader('X-Transport', self::CONFIG_TYPE);
            
            $this->mailerFactory->sendAsync($supportEmail);

        } catch (RuntimeException $e) {
            $this->logger?->error('Échec de la récupération de la configuration support', [
                'error' => $e->getMessage(),
            ]);
            throw $e;
        } catch (\Exception $e) {
            $this->logger?->error('Échec de l\'envoi de l\'email support', [
                'recipient' =>  $recipientEmail,
                'subject' => $subject,
                'error' => $e->getMessage(),
            ]);

            throw new RuntimeException(
                sprintf('Impossible d\'envoyer l\'email système : %s', $e->getMessage()),
                0,
                $e
            );
        }
    }
}