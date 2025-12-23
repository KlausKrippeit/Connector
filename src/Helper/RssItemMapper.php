<?php

namespace Connector\Helper;

use Connector\DTO\PodcastEpisode;

final class RssItemMapper
{
    public function map(\SimpleXMLElement $item): PodcastEpisode
    {
        return new PodcastEpisode(
            guid: (string) $item->guid,
            title: (string) $item->title,
            description: (string) $item->description,
            publishedAt: new \DateTimeImmutable((string) $item->pubDate),
            episodeUrl: (string) $item->link,
            audioUrl: (string) $item->enclosure['url'],
            audioSize: (int) $item->enclosure['length'],
            audioType: (string) $item->enclosure['type'],
        );
    }
}
