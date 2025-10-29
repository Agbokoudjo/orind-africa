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

namespace App\UI\Console;

use App\Domain\User\Enum\UserType;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Output\OutputInterface;
use App\Domain\User\Message\PromoteUserToSuperAdminCommand;
use App\Application\UseCase\User\PromoteUserToSuperAdminCommandHandler;

/**
 * @author AGBOKOUDJO Franck <internationaleswebservices@gmail.com>
 * @package <https://github.com/Agbokoudjo/>
 */
#[AsCommand(name: 'sonata:user:promote:to_super_admin', description: 'Promotes a user by adding a role')]
final class PromoteUserToSuperAdminCommandConsole extends Command
{
    public function __construct(private PromoteUserToSuperAdminCommandHandler $promoteUserToSuperAdmin)
    {
        parent::__construct('sonata:user:promote:to_super_admin');
    }

    public function __invoke(InputInterface $input, OutputInterface $output): int
    {
        $helper = new QuestionHelper('question');

        $question = new Question('Enter username: ');

        $question->setNormalizer(function (?string $value): string {
            return $value ?? '';
        });

        $question->setValidator(function (string $value): string {
            if ('' === trim($value)) {
                throw new \Exception('The username cannot be empty');
            }

            return $value;
        });
        $username = $helper->ask($input, $output, $question);

        $this->promoteUserToSuperAdmin->handle(UserType::ADMIN, new PromoteUserToSuperAdminCommand($username));

        return Command::SUCCESS;
    }
}
