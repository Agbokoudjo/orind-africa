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
use App\Domain\User\Enum\AccountStatus;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Output\OutputInterface;
use App\Infrastructure\Doctrine\Entity\User\AdminUser;
use App\Application\UseCase\User\ToggleUserAccountUseCase;
use Symfony\Component\Console\Question\ConfirmationQuestion;

/**
 * @author AGBOKOUDJO Franck <internationaleswebservices@gmail.com>
 * @package <https://github.com/Agbokoudjo/>
 */
#[AsCommand(name: 'sonata:user:activate', description: 'Activate a user')]
final class ToggleAdminUserAccountCommand extends Command
{
    public function __construct(private ToggleUserAccountUseCase $activateUser)
    {
        parent::__construct('sonata:user:activate');
    }

    public function __invoke(InputInterface $input, OutputInterface $output): int
    {
        
        $helper = new QuestionHelper();
       
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

        $question = new ConfirmationQuestion(
            \sprintf('Do you really want to activate "%s"? (yes/no) ', $username),
            true // valeur par défaut
        );

        $answer = $helper->ask($input, $output, $question);

        // Ici $answer est déjà un booléen
        $status = $answer ? AccountStatus::ACTIVE : AccountStatus::INACTIVE;

        try {
            $this->activateUser->handle(UserType::ADMIN,$username, $status);
        } catch (\InvalidArgumentException $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
            return Command::FAILURE;
        }

        if ($status === AccountStatus::ACTIVE) {
            $output->writeln(\sprintf('<info>User "%s" has been activated.</info>', $username));
        } else {
            $output->writeln(\sprintf('<comment>User "%s" activation was cancelled.</comment>', $username));
        }

        return Command::SUCCESS;
    }
}
