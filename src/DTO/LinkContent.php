<?php

namespace Connector\DTO;

class LinkContent
{
    public function __construct(
        public string $guid,
        public string $title,
        public string $description,
        public \DateTimeImmutable $publishedAt,
        public string $fileUrl,
        public int $fileSize,
        public string $fileType,
        public string $localPath,
        public string $status,
        public string $source,
    ) {}
}
