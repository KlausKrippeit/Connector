<?php
namespace Connector\Endpoint;

use DOMXPath;
use DOMDocument;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Connector\DTO\LinkContent;


class LinkEndpoint implements EndpointInterface
{
    private $html;

    public function __construct(
        private HttpClientInterface $client,
        private array $feeds,
    )
    {}

    public function getName(): string
    {
        return 'link';
    }

    public function fetch(): iterable
    {
        foreach ($this->feeds as $source => $config) {
            $response = $this->client->request('GET', $config['url']);
            $html = $response->getContent();
            $dom = new DOMDocument();
            $dom->loadHTML($html);

            $xpath = new DOMXPath($dom);

            $preNodes = $xpath->query('//a');

            foreach ($preNodes as $aLink) {
                yield new LinkContent(
                    guid: (string) $aLink->textContent,
                    title: $aLink->textContent,
                    description: $aLink->textContent,
                    publishedAt: new \DateTimeImmutable(),
                    fileUrl: (string) $aLink->getAttribute('href'),
                    fileSize: (int) $aLink->textContent,
                    fileType: (string) $aLink->textContent,
                    localPath: (string) $aLink->textContent,
                    status: (string) $aLink->textContent,
                    source: $aLink->textContent,
                );
            }
        }
    }
}
