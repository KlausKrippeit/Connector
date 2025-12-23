<?php
namespace Connector\Endpoint;

use Connector\Client\RssClient;

final class HeiseEndpoint implements EndpointInterface
{
    public function __construct(
        private RssClient $client
    ) {}

    public function getName(): string
    {
        return 'heise';
    }

    public function fetch(): iterable
    {
        return $this->client->fetch();
    }
}
