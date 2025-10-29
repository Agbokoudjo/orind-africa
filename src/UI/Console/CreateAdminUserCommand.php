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
use App\Domain\User\Message\UserCreateCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Output\OutputInterface;
use App\Application\UseCase\User\UserCreateCommandHandler;
use Symfony\Component\Console\Question\ConfirmationQuestion;

/**
 * @author AGBOKOUDJO Franck <internationaleswebservices@gmail.com>
 * @package <https://github.com/Agbokoudjo/>
 */
#[AsCommand(name: 'sonata:user:create', description: 'Create a user')]
final class CreateAdminUserCommand extends Command
{
    public function __construct(private UserCreateCommandHandler $adminCreateUser)
    {
        parent::__construct('sonata:user:create');
    }

    public function __invoke(InputInterface $input, OutputInterface $output): int
    {
        $helper = new QuestionHelper('question');

        $question = new Question('Enter username: ');
        $username = $helper->ask($input, $output, $question);

        $question = new Question('Enter email: ');
        $question->setValidator(function ($answer) {
            if (!filter_var($answer, FILTER_VALIDATE_EMAIL)) {
                throw new \RuntimeException('Invalid email address.');
            }
            return $answer;
        });
        $email = $helper->ask($input, $output, $question);

        $question = new Question('Enter password: ');
        $question->setNormalizer(function (?string $value): string {
            return $value ?? '';
        });
        $question->setValidator(function (string $value): string {
            if ('' === trim($value)) {
                throw new \Exception('The password cannot be empty');
            }

            return $value;
        });
        $question->setHidden(true);
        $question->setHiddenFallback(false);
        $password = $helper->ask($input, $output, $question);

        $question = new ConfirmationQuestion(
            sprintf('Do you want to activate the account "%s"? (yes/no) ', $username),
            true
        );

        /**
         * @var bool
         */
        $enabled = $helper->ask($input, $output, $question);

        // $question=new ConfirmationQuestion(\sprintf('Set the %s as super admin (yes/no)',$username));
        // $superAdmin =  $helper->ask($input, $output, $question);

        $this->adminCreateUser->handle(
            UserType::ADMIN,
            new UserCreateCommand(
                username: $username,
                email: $email, 
                password: $password,
                enabled: $enabled
                )
        );

        $output->writeln(sprintf(
            'Created user "%s" with email "%s" [%s]',
            $username,
            $email,
            $enabled ? 'enabled' : 'disabled'
        ));

        return Command::SUCCESS;
    }
}
