<?php
namespace Connector\Command;

use Connector\Entity\PodcastEpisodeEntity;
use Connector\Message\DownloadItemMessage;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Input\InputOption;
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

    private mixed $endpoint;
    private bool $dryRun;
    private bool $compare;
    private OutputInterface $output;

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
        $this
            ->addArgument('endpoint', InputArgument::OPTIONAL)
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Testlauf ohne echten Download')
            ->addOption('compare', null, InputOption::VALUE_NONE, 'Vergleicht vorher die vorandenen Folgen anhand der guid' );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // rclone move storage/podcast deltadroid:storage/podcast --progress
        // find storage/podcast/ -type f | wc -l
        // mariadb -u devData -p
        // bin/console messenger:consume async --limit=2000  -vv
        // bin/console connector:storage
        // bin/console connector:fetch-api podcast --dry-run
        // sudo nano /etc/fstab
        // select count(*) from podcast_episode\G
        // 2850
        // select count(*) from messenger_messages\G

        $this->output = $output;
        $this->endpoint = $input->getArgument('endpoint');
        $this->dryRun = $input->getOption('dry-run');
        $this->compare = $input->getOption('compare');

        $this->logger->info('Argument: ' . $this->endpoint);
        $this->logger->info('Dry-Run: ' . ($this->dryRun ? 'yes' : 'no'));
        $this->logger->info('Compare: ' . ($this->compare ? 'yes' : 'no'));

        $endpoints = $this->endpoint ? [$this->registry->get($this->endpoint)] : $this->registry->all();

        foreach ($endpoints as $endpoint) {
            foreach ($endpoint->fetch() as $dto) {

                if (!$this->isSource($dto->source, 'heise_show_sd')) {
                    //continue;
                }

                $guid = $this->getCleanGuid($dto->guid);
/*
                $output->writeln(sprintf('Fetch o: %s', $dto->guid));
                $output->writeln(sprintf('Fetch r: %s', $guid));
                continue;
*/
                $inArray = $this->compare ? $this->comapre($guid) : false;

                $inArray ? $dto->status = 'downloaded' : $dto->status = 'new';
                $entity = $this->saver->save($dto);
                /*
                $output->writeln(sprintf(
                    'DB: [%s] %s (%s)',
                    $entity->getPublishedAt()->format('Y-m-d H:i'),
                    $entity->getTitle(),
                    $entity->getStatus()
                )); */
                $this->dispatch($inArray === false, $entity, $output);
            }
        }

        return Command::SUCCESS;
    }

    private function comapre(string $guid): bool
    {
        $allreadyDownloadedCsv = '/home/deltadroid/podcast_list.csv';
        $allredayDownloaded = file_get_contents($allreadyDownloadedCsv);
        $existing = array_map('trim', explode(',', $allredayDownloaded));
        $inArray = in_array($guid, $existing);
        $this->output->writeln(sprintf('Guid: %s in array: %s', $guid, $inArray ? 'yes' : 'no'));

        return $inArray;
    }

    private function dispatch(bool $doDispatch, PodcastEpisodeEntity $entity, OutputInterface $output): void
    {
        if (!$doDispatch) {
            return;
        }

        $this->bus->dispatch(new DownloadItemMessage($entity->getGuid(), $this->dryRun));
        $output->writeln(sprintf(
            'Dispatched: [%s] %s (meta saved, download queued)',
            $entity->getPublishedAt()->format('Y-m-d H:i'),
            $entity->getTitle(),
        ));
    }

    private function isSource(string $source, string $target): bool
    {
        return $source == $target;
    }

    private function getCleanGuid(string $guid): string
    {
        if (str_starts_with($guid, 'http')) {

            $host = parse_url($guid, PHP_URL_HOST);
            $path = parse_url($guid, PHP_URL_PATH);
            $query = parse_url($guid, PHP_URL_QUERY);
            $guid = $host .''. $path .''. $query;

        }

        $guid = preg_replace('/\/\?(?!.*\/\?)/', '-', $guid); // "/?" remmove
        $guid = preg_replace('/\=(?!.*\=)/', '-', $guid); // "=" remove


        preg_match('/[\/\.]?([a-z0-9][A-Za-z0-9-]{10,})(?:\.(?:mp3|mp4))?$/', $guid, $matches);

        if (isset($matches[1])) {
            $guid = $matches[1];
        }

        return $guid;
    }
}
