<?php
namespace Connector\Endpoint;

use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

final class EndpointRegistry
{
    /**
     * @param iterable<EndpointInterface> $endpoints
     */
    public function __construct(
        #[AutowireIterator('connector.endpoint')]
        private iterable $endpoints
    ) {}

    public function get(string $name): EndpointInterface
    {
        foreach ($this->endpoints as $endpoint) {
            if ($endpoint->getName() === $name) {
                return $endpoint;
            }
        }

        throw new \InvalidArgumentException("Unknown endpoint: $name");
    }

    /**
     * @return iterable<EndpointInterface>
     */
    public function all(): iterable
    {
        exit;
        return $this->endpoints;
    }
}
