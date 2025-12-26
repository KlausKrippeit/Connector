<?php
namespace Connector\Service;

class GuidService
{

    public function getCleanGuid(string $guid): string
    {
        preg_match('/[\/\.]?([a-z0-9][A-Za-z0-9-]{10,})(?:\.(?:mp3|mp4))?$/', $guid, $matches);

        if (isset($matches[1])) {
            $guid = $matches[1];
        }
        return $guid;
    }
}
