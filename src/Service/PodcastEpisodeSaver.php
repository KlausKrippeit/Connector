<?php

namespace Connector\Service;

use Connector\DTO\PodcastEpisode as PodcastEpisodeDTO;
use Connector\Entity\PodcastEpisodeEntity as PodcastEpisodeEntity;
use Connector\Repository\PodcastEpisodeEntityRepository;
use Doctrine\ORM\EntityManagerInterface;

final class PodcastEpisodeSaver
{
    public function __construct(
        private PodcastEpisodeEntityRepository $repository,
        private EntityManagerInterface $em
    ) {}

    public function save(PodcastEpisodeDTO $dto): PodcastEpisodeEntity
    {
        // Prüfen ob GUID schon existiert
        $existing = $this->repository->findByGuid($dto->guid);

        if ($existing) {
            // Metadaten aktualisieren
            $existing->setTitle($dto->title);
            $existing->setDescription($dto->description);
            $existing->setPublishedAt($dto->publishedAt);
            $existing->setEpisodeUrl($dto->episodeUrl);
            $existing->setAudioUrl($dto->audioUrl);
            $existing->setAudioSize($dto->audioSize);
            $existing->setAudioType($dto->audioType);
            //$existing->setStatus('new'); // Reset falls nötig
            $existing->setSource($dto->source);

            $this->em->flush();

            return $existing;
        }

        // Neue Episode anlegen
        $entity = new PodcastEpisodeEntity();
        $entity->setGuid($dto->guid);
        $entity->setTitle($dto->title);
        $entity->setdescription($dto->description);
        $entity->setPublishedAt($dto->publishedAt);
        $entity->setEpisodeUrl($dto->episodeUrl);
        $entity->setAudioUrl($dto->audioUrl);
        $entity->setAudioSize($dto->audioSize);
        $entity->setAudioType($dto->audioType);
        $entity->setStatus('new');
        $entity->setSource($dto->source);

        $this->em->persist($entity);
        $this->em->flush();

        return $entity;
    }
}
