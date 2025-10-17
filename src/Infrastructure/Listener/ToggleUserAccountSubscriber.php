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

namespace App\Infrastructure\Listener;

use Symfony\Component\Mime\Email;
use App\Domain\User\Event\ToggleUserAccountEvent;
use App\Infrastructure\Service\Mailing\SystemMailer;
use App\Infrastructure\Service\UserAccountRouteResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use App\Application\Listener\ToggleUserAccountEventSubscriberInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

/**
 * @internal
 * @author AGBOKOUDJO Franck <internationaleswebservices@gmail.com>
 * @package <https://github.com/Agbokoudjo/>
 */
final class ToggleUserAccountSubscriber implements EventSubscriberInterface, ToggleUserAccountEventSubscriberInterface
{
    public function __construct(
        private readonly SystemMailer $system_mailer,
        private readonly ParameterBagInterface $parameterServices,
        private readonly UrlGeneratorInterface $router,
        private readonly UserAccountRouteResolver $userAccountRouteResolver) {}

    public static function getSubscribedEvents(): array
    {
        return [
            ToggleUserAccountEvent::class=> 'onToggleUserAccountEvent'
        ];
    }

    public function onToggleUserAccountEvent(ToggleUserAccountEvent $event): void{

        if($_ENV['APP_EXECUTE_LISTENER'] === "false"){return ;}
        
        $user=$event->getUser();
        $login = $this->userAccountRouteResolver->resolveLoginRouteName($user);
        // On envoie un email a l'utilisateur pour l'informer que son compte est activer ou desactiver
        $email = $this->system_mailer->createEmail('email/profile/toggle_user_account.html.twig', [
            'status' =>$user->getStatus(),
            'username' =>$user->getUsername(),
            "url_login"=>$this->router->generate($login,[],UrlGeneratorInterface::ABSOLUTE_URL)
        ])
        ;
        $subject = ($user->getStatus() === true)
                ? \sprintf('Votre compte %s a été activé',$this->parameterServices->get('NAME_SITE'))
                : \sprintf('Votre compte %s a été désactivé', $this->parameterServices->get('NAME_SITE'));

            $email->to($user->getEmail())
            ->priority(Email::PRIORITY_HIGH)
            ->subject($subject);
        $this->system_mailer->send($email);
    }
}