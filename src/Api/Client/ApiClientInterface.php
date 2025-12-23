<?php 
namespace Connector\Api\Client;

interface ApiClientInterface
{
    public function getName(): string;

    public function fetch(): iterable;
}
