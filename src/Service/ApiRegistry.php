<?php
namespace Connector\Service;

use Connector\Api\Client\ApiClientInterface;

class ApiRegistry
{
    /** @var ApiClientInterface[] */
    private array $clients = [];

    public function __construct(iterable $clients)
    {
        foreach ($clients as $client) {
            $this->clients[$client->getName()] = $client;
        }
    }

    public function get(string $name): ApiClientInterface
    {
        return $this->clients[$name];
    }

    public function all(): iterable
    {
        return $this->clients;
    }
}

