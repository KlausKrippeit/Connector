<?php
namespace Connector\DTO;

final class NewsItem
{
    public function __construct(
        public string $id,
        public string $title,
        public string $url,
        public \DateTimeImmutable $publishedAt,
    ) {}
}
