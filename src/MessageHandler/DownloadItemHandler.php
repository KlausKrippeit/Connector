<?php

namespace Connector\MessageHandler;

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

            $finalFile = $sourceDir . '/' . $item->getGuid() . '.mp3';
            $tmpFile = tempnam(sys_get_temp_dir(), 'podcast_');
            $response = $this->client->request('GET', $item->getAudioUrl());
            file_put_contents($tmpFile, $response->getContent());

            rename($tmpFile, $finalFile);

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
}
