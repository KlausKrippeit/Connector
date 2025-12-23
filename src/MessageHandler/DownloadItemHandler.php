<?php

namespace Connector\MessageHandler;

use Connector\Message\DownloadItemMessage;
use Connector\Repository\PodcastEpisodeEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
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
    ) {}

    public function __invoke(DownloadItemMessage $message): void
    {
        $item = $this->repository->findOneByStatusField('new',$message->guid);

        if (!$item) {
            return; // Entity existiert nicht
        }

        if (!$item || $item->getStatus() === 'downloaded') {
            return; // idempotent
        }

        try {
            $response = $this->client->request('GET', $item->getAudioUrl());

            $sourceDir = rtrim($this->downloadDir, '/') . '/' . $item->source;

            if (!is_dir($sourceDir)) {
                mkdir($sourceDir, 0775, true);
            }

            $tmpFile = tempnam(sys_get_temp_dir(), 'podcast_');
            $response = $this->client->request('GET', $item->getAudioUrl());
            file_put_contents($tmpFile, $response->getContent());

            $finalFile = $sourceDir . '/' . $item->getGuid() . '.mp3';
            rename($tmpFile, $finalFile);

            $item->setLocalPath($finalFile);
            $item->setStatus('downloaded');
            $this->em->flush();
        } catch (\Throwable $e) {
            $item->setStatus('failed');
            $this->em->flush();

            throw $e; // Messenger retry / DLQ
        }
    }
}
