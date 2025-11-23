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

use App\Infrastructure\Security\Encryption\IdEncryptionService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Commande pour générer la clé d'encryption des IDs
 * 
 * Usage :
 * php bin/console app:generate-encryption-key
 * 
 * @author AGBOKOUDJO Franck
 */
#[AsCommand(
    name: 'app:generate-encryption-key',
    description: 'Génère une clé d\'encryption pour les IDs Sonata',
    hidden: false
)]
final class GenerateEncryptionKeyCommand extends Command
{
    public function __construct()
    {
        parent::__construct('app:generate-encryption-key');
    }

    public function __invoke(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('🔐 Générateur de clé d\'encryption');

        try {
            // Générer la clé
            $key = IdEncryptionService::generateEncryptionKey();

            $io->newLine();
            $io->success('✅ Clé générée avec succès !');
            $io->newLine();

            $io->section('📋 Ajoute cette ligne dans ton fichier .env :');
            $io->writeln('');
            $io->writeln($this->formatKeyLine($key));
            $io->writeln('');

            $io->note([
                'Cette clé doit être gardée SECRÈTE',
                'Ne la commit pas sur le repository',
                'Utilise .env.local ou des variables d\'environnement',
            ]);

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error('Erreur lors de la génération : ' . $e->getMessage());
            return Command::FAILURE;
        }
    }

    private function formatKeyLine(string $key): string
    {
        return sprintf('APP_ID_ENCRYPTION_KEY="%s"', $key);
    }
}