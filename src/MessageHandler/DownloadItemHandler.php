<?php

namespace Connector\MessageHandler;

use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use Connector\Message\DownloadItemMessage;
use Connector\Repository\PodcastEpisodeEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[AsMessageHandler]
final class DownloadItemHandler
{
    public function __construct(
        private PodcastEpisodeEntityRepository $repository,
        private EntityManagerInterface $em,
        private HttpClientInterface $client,
        #[Autowire('%connector.download_dir%')]
        private string $downloadDir,
        private LoggerInterface $logger,
    ) {}

    public function __invoke(DownloadItemMessage $message): void
    {
        $item = $this->repository->findOneByStatusField('new',$message->guid);

        if (!$item) {
            return; // Entity existiert nicht
        }

        try {
            $sourceDir = rtrim($this->downloadDir, '/') . '/' . $item->source;

            if (!is_dir($sourceDir)) {
                mkdir($sourceDir, 0775, true);
            }

            $freeSpace = disk_free_space($sourceDir);
            $this->logger->info('FreeSpace: ' . $this->formatBytes($freeSpace));
            $this->logger->info('url: ' . $item->getAudioUrl());
            $guid = $item->getGuid();

            // TODO: service classs !!!!!!
            if (str_starts_with($guid, 'http')) {
                $host = parse_url($guid, PHP_URL_HOST);
                $path = parse_url($guid, PHP_URL_PATH);
                $query = parse_url($guid, PHP_URL_QUERY);
                if ($path !== '') {
                    preg_match('/[\/\.]?([a-z0-9][A-Za-z0-9-_]{10,})(?:\.(?:mp3|mp4))?$/', $path, $matches);
                    if (isset($matches[1])) {
                        $path = $matches[1];
                    }
                }
                $guid = $host .''. $path .''. $query;
                $guid = str_replace('/', '', $guid);
            }

            $guid = preg_replace('/\/\?(?!.*\/\?)/', '-', $guid);
            $guid = preg_replace('/\=(?!.*\=)/', '-', $guid);

            preg_match(
                '/[\/\.]?([a-z0-9][A-Za-z0-9-_]{10,})(?:\.(?:mp3|mp4))?$/',
                $guid,
                $matches
            );

            if (isset($matches[1])) {
                $guid = $matches[1];
            }

            $this->logger->info('FreeSpace: match' . json_encode($guid));
            $path = parse_url($item->getAudioUrl(), PHP_URL_PATH);
            $extension = pathinfo($path, PATHINFO_EXTENSION);

            $finalFile = $sourceDir . '/' . $guid . '.' . $extension;

            if ($message->dryRun) {

                $dummDownloadDir = '/home/deltadroid/storagebox/dummy';
                $sourceDir = rtrim($dummDownloadDir, '/') . '/' . $item->source;
                $finalFile = $sourceDir . '/' . $guid . '.' . $extension;

                $this->createDummyFile($finalFile);

                $item->setLocalPath($finalFile);
                $item->setStatus('dry-run');
                $this->em->flush();
                return;
            }

            $this->logger->info('get origin');
            $tmpFile = tempnam(sys_get_temp_dir(), 'podcast_');
            $response = $this->client->request('GET', $item->getAudioUrl());
            if ($response->getStatusCode() != 200) {
                $this->logger->info('not found: ' . $item->getAudioUrl());
                try {
                    $resFallback = $this->client->request('GET', $item->getGuid());
                } catch (\Exception $e) {
                    $this->logger->error('guid failed: '. $item->getGuid());
                    throw $e;
                }
                if ($resFallback->getStatusCode() != 200) {
                    $this->logger->info('Not found resFallback: ' . $item->getGuid());
                }

                $response = $resFallback;
            }

            try {
                $fp = fopen($tmpFile, 'w');

                foreach ($this->client->stream($response) as $chunk) {
                    if ($chunk->isTimeout()) {
                        continue;
                    }

                    fwrite($fp, $chunk->getContent());
                }

                fclose($fp);
                $this->logger->info('get origin - success');
            } catch (\Exception $e) {
                $this->logger->error('not found: ' . $response->getInfo() . '## '  . $e->getMessage());
                throw $e;
            }
            $this->logger->info('rclone moveto');
            //rename($tmpFile, $finalFile);
            $process = new Process([
                'rclone',
                '--config',
                '/home/deltadroid/.config/rclone/rclone.conf',
                'moveto',
                $tmpFile,
                'deltadroid:storage/podcast/' . $item->source . '/' . $guid . '.' . $extension,
                '--stats-one-line',
            ]);

            $process->setTimeout(300); // 5 Minuten
            $process->run();
            // Fehler?
            if (!$process->isSuccessful()) {
                throw new ProcessFailedException($process);
            }
            $this->logger->info('success');

            $item->setLocalPath($finalFile);
            $item->setStatus('downloaded');
            $this->em->flush();

            $this->logger->info(sprintf('Pfad: %s',$finalFile));

        } catch (\Throwable $e) {
            $item->setStatus('failed');
            $this->em->flush();

            throw $e; // Messenger retry / DLQ
        }
    }

    private function createDummyFile(string $path): void
    {
        $dir = dirname($path);
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        $content = sprintf(
            "DRY RUN FILE\nCreated at: %s\n",
            (new \DateTimeImmutable())->format('c')
        );

        file_put_contents($path, $content);
    }

    function formatBytes(float $bytes, int $precision = 2): string {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));

        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
