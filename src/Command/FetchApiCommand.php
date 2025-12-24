<?php
namespace Connector\Command;

use Connector\Message\DownloadItemMessage;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
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
        private PodcastEpisodeSaver $saver,
        private MessageBusInterface $bus,
        private LoggerInterface $logger,
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
        $allredayDownloadedCsv = '/home/deltadroid/podcast_list.csv';
        $allredayDownloaded = file_get_contents($allredayDownloadedCsv);
        $existing = array_map('trim', explode(',', $allredayDownloaded));

        foreach ($endpoints as $endpoint) {
            foreach ($endpoint->fetch() as $dto) {

                $inArray = in_array($dto->guid, $existing);
                $output->writeln(sprintf('Guid: %s in array: %s', $dto->guid, $inArray ? 'yes' : 'no'));

                $inArray ? ($dto->status = 'downloaded') : ($dto->status = 'new');
                
                $entity = $this->saver->save($dto);
                
                $output->writeln(sprintf(
                    'DB: [%s] %s (%s)',
                    $entity->getPublishedAt()->format('Y-m-d H:i'),
                    $entity->getTitle(),
                    $entity->getStatus()
                ));

                if ($inArray) {
                    continue;
                }

                $this->bus->dispatch(new DownloadItemMessage($entity->getGuid()));
                $output->writeln(sprintf(
                    'Dispatched: [%s] %s (meta saved, download queued)',
                    $entity->getPublishedAt()->format('Y-m-d H:i'),
                    $entity->getTitle(),
                ));
            }
        }
        
        return Command::SUCCESS;
    }
}

