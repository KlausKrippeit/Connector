<?php
namespace Connector\Endpoint;

interface EndpointInterface
{
    public function getName(): string;

    /**
     * @return iterable<object>
     */
    public function fetch(): iterable;
}
