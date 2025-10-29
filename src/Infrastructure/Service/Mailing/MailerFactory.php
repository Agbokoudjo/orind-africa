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
use InvalidArgumentException;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Message;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mime\Crypto\DkimSigner;
use App\Infrastructure\Service\Mailing\PriorityInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;

/**
 * Implémentation du factory pour la création et l'envoi d'emails.
 * 
 * Supporte l'envoi asynchrone via queue, la signature DKIM,
 * et la gestion de multiples configurations d'expéditeurs.
 *
 * @author AGBOKOUDJO Franck <internationaleswebservices@gmail.com>
 * @package App\Infrastructure\Service\Mailing
 */
final class MailerFactory implements MailerFactoryInterface,PriorityInterface
{
    /**
     * @param string|null $dkimKey Chemin vers la clé privée DKIM
     * @param array<string, array{address: string, name: string}> $fromAddresses Configuration des expéditeurs
     */
    public function __construct(
        private readonly MailerInterface $mailer,
        private readonly ?string $dkimKey = null,
        private readonly string $domainName = '',
        private readonly array $fromAddresses = []
    ) {}

    /**
     * {@inheritdoc}
     */
    public function sendAsync(Email $email): void // 🚨 NOUVEAU PARAMÈTRE 🚨
    {
        $this->sendNow($email);
    }

    /**
     * {@inheritdoc}
     */
    public function sendNow(Email $email): void
    {
        try {
            // 2. Appliquer la signature DKIM (si nécessaire)
            $messageToSend = $this->applyDkimSignature($email);
            $messageToSend = $this->applyDkimSignature($email);
            // 3. Envoyer via le transport sélectionné
            $this->mailer->send($messageToSend);
        } catch (TransportExceptionInterface $e) {
            throw new RuntimeException(
                sprintf('Échec de l\'envoi de l\'email: %s', $e->getMessage()),
                0,
                $e
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function createTemplateEmail(
        string $senderAddress,
        string $senderName,
        string $recipientAddress,
        string $subject,
        string $templatePath,
        ?array $context
    ): Email {
        $this->validateEmailAddress($senderAddress, 'senderAddress');
        $this->validateEmailAddress($recipientAddress, 'recipientAddress');

        $this->validateSubject($subject);

        $templateEmail = (new TemplatedEmail())
            ->from(new Address($senderAddress, $senderName))
            ->to(new Address($recipientAddress))
            ->subject($subject)
            ->htmlTemplate($templatePath);

        if ($context !== null && !empty($context)) {
            $templateEmail->context($context);
        }

        return $templateEmail;
    }

    /**
     * @return Email
     */
    public function addTextHeader(Email $email ,string $type, string $name, $body): Email
    {
        $email->getHeaders()->addTextHeader($type, $name, $body);

        return $email;
    }

    /**
     * Applique la signature DKIM si configurée.
     *
     * @param Email $email L'email à signer
     * @return Email|Message L'email signé ou l'email original
     */
    private function applyDkimSignature(Email $email): Email|Message
    {
        if ($this->dkimKey === null || empty($this->domainName)) {
            return $email;
        }

        try {
            $keyPath = str_starts_with($this->dkimKey, 'file://')
                ? $this->dkimKey
                : "file://{$this->dkimKey}";

            $dkimSigner = new DkimSigner($keyPath, $this->domainName, 'default');
            $message = new Message($email->getPreparedHeaders(), $email->getBody());

            return $dkimSigner->sign($message);
        } catch (\Exception $e) {
            // Log l'erreur mais continue sans DKIM plutôt que de bloquer l'envoi
            error_log(sprintf('Échec signature DKIM: %s', $e->getMessage()));
            return $email;
        }
    }

    /**
     * Valide qu'une chaîne est une adresse email valide.
     *
     * @param string $email L'adresse à valider
     * @param string $paramName Le nom du paramètre (pour les messages d'erreur)
     * 
     * @throws InvalidArgumentException Si l'email est invalide
     */
    private function validateEmailAddress(string $email, string $paramName): void
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException(
                sprintf('Le paramètre "%s" doit être une adresse email valide. Reçu: "%s"', $paramName, $email)
            );
        }
    }

    /**
     * Récupère la configuration d'un expéditeur par type.
     *
     * @param string $type Le type d'expéditeur (ex: 'system', 'noreply', 'support')
     * 
     * @return array{address: string, name: string} Configuration de l'expéditeur
     * 
     * @throws RuntimeException Si le type n'existe pas
     */
    public function fromConfig(string $type = 'system'): array
    {
        if (!isset($this->fromAddresses[$type])) {
            throw new RuntimeException(
                sprintf(
                    'Type d\'expéditeur "%s" inconnu. Types disponibles: %s',
                    $type,
                    implode(', ', array_keys($this->fromAddresses))
                )
            );
        }

        return $this->fromAddresses[$type];
    }

    /**
     * Valide que le sujet n'est pas vide.
     *
     * @throws InvalidArgumentException Si le sujet est vide
     */
    private function validateSubject(string $subject): void
    {
        if (trim($subject) === '' && empty($subject)) {
            throw new InvalidArgumentException('Le sujet de l\'email ne peut pas être vide');
        }
    }

    public function validatePriority(int $priority): void
    {
        if ($priority < self::PRIORITY_HIGHEST || $priority > self::PRIORITY_LOWEST) {
            throw new InvalidArgumentException(
                sprintf(
                    'La priorité doit être entre %d et %d. Reçu: %d',
                    self::PRIORITY_HIGHEST,
                    self::PRIORITY_LOWEST,
                    $priority
                )
            );
        }
    }
}
