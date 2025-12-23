<?php
namespace Connector\Api\Client;

use Symfony\Contracts\HttpClient\HttpClientInterface;

abstract class AbstractApiClient implements ApiClientInterface
{
    public function __construct(
        protected HttpClientInterface $httpClient
    ) {}
}

