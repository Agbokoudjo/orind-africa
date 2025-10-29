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

use App\Domain\User\Event\PromoteUserEvent;
use App\Domain\User\Event\PromoteUserToSuperAdminEvent;
use App\Infrastructure\Service\UserAccountRouteResolver;
use App\Application\Listener\PromoteUserSubscriberInterface;
use App\Infrastructure\Service\Mailing\EmailSenderInterface;
use App\Infrastructure\Service\Mailing\PriorityInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

/**
 * @author AGBOKOUDJO Franck <internationaleswebservices@gmail.com>
 * @package <https://github.com/Agbokoudjo/>
 */
final class PromoteUserSubscriber implements EventSubscriberInterface,PromoteUserSubscriberInterface
{
    public function __construct(
        #[Autowire('app.system.mailer')]
        private readonly EmailSenderInterface $systemMailer,
        private readonly ParameterBagInterface $parameterServices,
        private readonly UrlGeneratorInterface $router
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            PromoteUserToSuperAdminEvent::class => 'onPromoteUserToSuperAdminEvent',
            PromoteUserEvent::class=>'onPromoteUserEvent'
        ];
    }

    public function onPromoteUserToSuperAdminEvent(PromoteUserToSuperAdminEvent $event): void
    {
       /* $_userEvent=$event->getUser() ;

        try {
            $login_url=$this->router->generate(
                UserAccountRouteResolver::resolveLoginRouteNameByUser($_userEvent),
                [],
                UrlGeneratorInterface::ABSOLUTE_URL
            );

            $this->systemMailer->send(
                $_userEvent->getEmail(),
                'Vous etes promis comme super administrateur',//subject
                'email/promote/super_admin.html.twig',
                [
                    'username'=>$_userEvent->getUsername(),
                    'promote_date'=>$_userEvent->getUpdatedAt()->format('Y-m-d'),
                    'app_admin_login'=> $login_url

                ],
                PriorityInterface::PRIORITY_HIGH
                );

        } catch (\Throwable $th) {
            //throw $th;
        }*/
    }

    public function onPromoteUserEvent(PromoteUserEvent $event): void {
        
    }
}
