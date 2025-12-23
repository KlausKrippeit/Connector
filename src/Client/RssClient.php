<?php

namespace Connector\Client;

use Connector\DTO\PodcastEpisode;
use Connector\Helper\RssItemMapper;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class RssClient
{
    public function __construct(
        private HttpClientInterface $httpClient,
        private RssItemMapper $mapper,
    ) {}

    /**
     * @return iterable<PodcastEpisode>
     */
    public function fetch(): iterable
    {
        $response = $this->httpClient->request(
            'GET',
            'https://bit-rauschen.podigee.io/feed/mp3'
        );

        $xml = new \SimpleXMLElement($response->getContent());

        foreach ($xml->xpath('//item') as $item) {
            yield $this->mapper->map($item);
        }
    }
}
