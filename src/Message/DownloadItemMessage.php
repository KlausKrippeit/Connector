<?php

namespace Connector\Message;

final class DownloadItemMessage
{
    public function __construct(
        public string $guid,
        public bool $dryRun,
    ) {}
}
