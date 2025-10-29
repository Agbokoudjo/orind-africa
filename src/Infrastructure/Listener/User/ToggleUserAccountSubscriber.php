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

namespace App\Infrastructure\Listener\User;

use Psr\Log\LoggerInterface;
use App\Domain\User\Event\ToggleUserAccountEvent;
use App\Infrastructure\Service\UserAccountRouteResolver;
use App\Infrastructure\Service\Mailing\PriorityInterface;
use App\Infrastructure\Service\Mailing\EmailSenderInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use App\Application\Listener\ToggleUserAccountEventSubscriberInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

/**
 * Subscriber pour gérer les notifications lors de l'activation/désactivation de comptes utilisateurs.
 * 
 * Envoie automatiquement un email à l'utilisateur pour l'informer du changement
 * de statut de son compte (activé ou désactivé).
 *
 * @internal Ce subscriber est appelé automatiquement par l'EventDispatcher
 * @author AGBOKOUDJO Franck <internationaleswebservices@gmail.com>
 * @package App\Infrastructure\Listener
 */
final class ToggleUserAccountSubscriber implements EventSubscriberInterface, ToggleUserAccountEventSubscriberInterface
{
    public function __construct(
        #[Autowire('app.system.mailer')]
        private readonly EmailSenderInterface $systemMailer,
        private readonly ParameterBagInterface $parameterBag,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly LoggerInterface $logger,
       ) {}

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            ToggleUserAccountEvent::class=> 'onToggleUserAccountEvent'
        ];
    }

    /**
     * Gère l'événement de changement de statut du compte utilisateur.
     *
     * Envoie un email de notification à l'utilisateur concerné.
     *
     * @param ToggleUserAccountEvent $event L'événement contenant les informations du compte
     * 
     * @return void
     */
    public function onToggleUserAccountEvent(ToggleUserAccountEvent $event): void
    {
        if (!$this->parameterBag->get('app.execute_listener')) {

            $this->logger?->debug('Listener désactivé via configuration');
            
            return;
        }

        $user = $event->getUser();

        try {
            $siteName = $this->parameterBag->get('app.site_name');
            $loginRoute = UserAccountRouteResolver::resolveLoginRouteNameByUser($user);
            $loginUrl = $this->urlGenerator->generate(
                $loginRoute,
                [],
                UrlGeneratorInterface::ABSOLUTE_URL
            );

            $subject = $this->buildEmailSubject($user->getStatus(), $siteName);

            $context_twig=[
                    'status' => $user->getStatus(),
                    'username' => $user->getUsername(),
                    'url_login' => $loginUrl,
                    'NAME_SITE' => $siteName
                ];
            
            if($user->isEnabled()){
                $context_data['password_user']= $event->getPlainPassword() ;
            }
            
            $this->systemMailer->send(
                $user->getEmail(),
                $subject,
                'email/profile/toggle_user_account.html.twig',
                $context_twig,
                PriorityInterface::PRIORITY_HIGH 
            );

            $this->logger?->info('Email de changement de statut envoyé', [
                'user_email' => $user->getEmail(),
                'new_status' => $user->getStatus() ? 'activé' : 'désactivé',
            ]);
            
        } catch (\Exception $e) {
            $this->logger?->error('Échec de l\'envoi de l\'email de changement de statut', [
                'user_email' => $user->getEmail(),
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Construit le sujet de l'email selon le statut du compte.
     *
     * @param bool $isActive Le statut du compte (true = activé, false = désactivé)
     * @param string $siteName Le nom du site
     * 
     * @return string Le sujet de l'email
     */
    private function buildEmailSubject(bool $isActive, string $siteName): string
    {
        return $isActive
            ? sprintf('Votre compte %s a été activé', $siteName)
            : sprintf('Votre compte %s a été désactivé', $siteName);
    }
}