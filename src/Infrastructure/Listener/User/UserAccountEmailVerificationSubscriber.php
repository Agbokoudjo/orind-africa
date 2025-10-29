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

use App\Infrastructure\Service\Mailing\PriorityInterface;
use App\Domain\User\Event\UserAccountEmailVerificationEvent;
use App\Infrastructure\Service\Mailing\EmailSenderInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

final class UserAccountEmailVerificationSubscriber implements EventSubscriberInterface
{
    public function __construct(
        #[Autowire('app.system.mailer')]
        private EmailSenderInterface $emailSender,
        private UrlGeneratorInterface $router,
        private ParameterBagInterface $params
    ) {}
    public static function getSubscribedEvents(): array
    {
        return [
            UserAccountEmailVerificationEvent::class => 'onUserAccountCreated',
        ];
    }

    public function onUserAccountCreated(UserAccountEmailVerificationEvent $event):void{

        $encodedUserType = base64_encode($event->getUserType()->value);

        $confirmationUrl=$this->router->generate(
            'app.verify.email',[
                'token' => $event->getRawToken(),
                'slug'=>$event->getSlug(),
                'user_type'=> $encodedUserType 
            ],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        $siteName = $this->params->get('app.site_name');

        $subject = \sprintf('Votre compte %s a été créé', $siteName);

        $this->emailSender->send(
            $event->getEmail(),
            $subject,
            'emails/profile/admin_account_confirmation.html.twig',
            [
                'email' => $event->getEmail(),
                'username'=> $event->getUsername(),
                'confirmationUrl' => $confirmationUrl,
                'siteName' => $siteName,
            ],
            PriorityInterface::PRIORITY_HIGH
        );
    }
}
