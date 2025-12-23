<?php
namespace Connector\Command;

use Connector\Client\RssClient;
use Connector\Endpoint\EndpointRegistry;
use Connector\Service\PodcastEpisodeSaver;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'connector:fetch-api',
)]
final class FetchApiCommand extends Command
{
    public function __construct(
        private EndpointRegistry $registry,
        private PodcastEpisodeSaver $saver
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('endpoint', InputArgument::OPTIONAL);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $name = $input->getArgument('endpoint');

        $endpoints = $name ? [$this->registry->get($name)] : $this->registry->all();

        foreach ($endpoints as $endpoint) {
            foreach ($endpoint->fetch() as $item) {
                dump($item);
            }
        }

        foreach ($endpoint->fetch() as $dto) {
            $entity = $this->saver->save($dto);

            $output->writeln(sprintf(
                '[%s] %s (%s)',
                $entity->getPublishedAt()->format('Y-m-d H:i'),
                $entity->getTitle(),
                $entity->getStatus()
            ));
        }

        return Command::SUCCESS;
    }
}

