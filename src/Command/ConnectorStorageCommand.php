<?php

namespace Connector\Command;

use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'connector:storage',
    description: 'Add a short description for your command',
)]
class ConnectorStorageCommand extends Command
{
    public function __construct()
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $baseDir = '/home/deltadroid/storagebox/storage/podcast/';

        $files = [];

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($baseDir, FilesystemIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $files[] = pathinfo($file->getFilename(), PATHINFO_FILENAME);
            }
        }

        file_put_contents('/home/deltadroid/podcast_list.csv', implode(', ', $files));

        $io->success(sprintf(
            'Files: %s',
            implode(', ', $files)
        ));

        return Command::SUCCESS;
    }
}
