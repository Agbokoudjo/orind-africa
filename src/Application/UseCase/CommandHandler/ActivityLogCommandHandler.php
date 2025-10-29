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

namespace App\Application\UseCase\CommandHandler;

use App\Domain\Log\ActivityLog;
use App\Domain\Log\Command\ActivityLogCommand;
use App\Domain\Log\Manager\ActivityLogManagerInterface;

/**
 * @author AGBOKOUDJO Franck <internationaleswebservices@gmail.com>
 * @package <https://github.com/Agbokoudjo/>
 */
final class ActivityLogCommandHandler 
{
    public function __construct(
        private ActivityLogManagerInterface $activityLogManager){}
    
    public function handle(ActivityLogCommand $activityCommand):void{
        
        try {
            /**
             * @var ActivityLog
             */
            $activityLog=$this->activityLogManager->create();

            $activityLog
                ->setUserContext($activityCommand->userContext)
                ->setIpAddress($activityCommand->ipAddress)
                ->setContext($activityCommand->context)
                ->setAction($activityCommand->action)
                ->setMethod($activityCommand->method)
                ->setRoute($activityCommand->route)
                ->setCreatedAt(new \DateTimeImmutable('now'),new \DateTimeZone('UTC'))
                ;
            $this->activityLogManager->save($activityLog,true);

        } catch (\Exception $exception) {
            throw $exception;
        }
    }
}
