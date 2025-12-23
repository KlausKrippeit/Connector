<?php
namespace Connector\DTO;

final readonly class NewsItem
{
    public function __construct(
        public string $id,
        public string $title,
        public string $url,
        public \DateTimeImmutable $publishedAt,
    ) {}
}
