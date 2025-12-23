<?php

namespace Connector\DTO;

final readonly class PodcastEpisode
{
    public function __construct(
        public string $guid,
        public string $title,
        public string $description,
        public \DateTimeImmutable $publishedAt,
        public string $episodeUrl,
        public string $audioUrl,
        public int $audioSize,
        public string $audioType,
    ) {}
}
