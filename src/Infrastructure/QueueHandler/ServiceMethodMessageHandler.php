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

namespace App\Infrastructure\QueueHandler;

use Psr\Container\ContainerInterface;
use Symfony\Component\Mailer\MailerInterface;
use App\Application\UseCase\LoggerLoginUseCase;
use App\Infrastructure\Service\Mailing\SystemMailer;
use App\Application\Queue\Message\ServiceMethodMessage;
use Symfony\Contracts\Service\ServiceSubscriberInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use App\Application\QueueHandler\ServiceMethodMessageHandlerInterface;

/**
 * @author AGBOKOUDJO Franck <internationaleswebservices@gmail.com>
 * @package <https://github.com/Agbokoudjo/>
 */
#[AsMessageHandler(fromTransport:"async",handles:ServiceMethodMessage::class)]
final class ServiceMethodMessageHandler implements ServiceSubscriberInterface,ServiceMethodMessageHandlerInterface
{
    public function __construct(private readonly ContainerInterface $container) {}

    public function __invoke(ServiceMethodMessage $message): void
    {
       $this->__handler($message);
    }
    public static function getSubscribedServices(): array
    {
        return [
            SystemMailer::class=>SystemMailer::class,
            MailerInterface::class => MailerInterface::class,
            LoggerLoginUseCase::class=> LoggerLoginUseCase::class
        ];
    }
    private function __handler(ServiceMethodMessage $message): mixed
    {
        /** @var callable $callable */
        $callable = [
            $this->container->get($message->getServiceName()),
            $message->getMethod(),
        ];

       return  call_user_func_array($callable, $message->getParams());
    }
}