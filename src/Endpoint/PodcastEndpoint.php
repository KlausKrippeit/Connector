<?php
namespace Connector\Endpoint;

use Connector\DTO\PodcastEpisode;
use SimpleXMLElement;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class PodcastEndpoint implements EndpointInterface
{
    public function __construct(
        private HttpClientInterface $client,
        private array $feeds, 
    ) {}

    public function getName(): string
    {
        return 'podcast';
    }

    public function fetch(): iterable
    {
        foreach ($this->feeds as $source => $config) {
            $response = $this->client->request('GET', $config['url']);

            $xml = new SimpleXMLElement($response->getContent());

            foreach ($xml->xpath('//item') as $item) {
                yield new PodcastEpisode(
                    guid: (string) $item->guid,
                    title: (string) $item->title,
                    description: (string) $item->description,
                    publishedAt: new \DateTimeImmutable((string) $item->pubDate),
                    episodeUrl: (string) $item->link,
                    audioUrl: (string) $item->enclosure['url'],
                    audioSize: (int) $item->enclosure['length'],
                    audioType: (string) $item->enclosure['type'],
                    status: (string) '',
                    source: $source,
                );
            }
        }
    }
}
