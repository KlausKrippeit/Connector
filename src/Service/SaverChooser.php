<?php

namespace Connector\Service;

class SaverChooser
{
    private string $option;
    public function __construct(
        private PodcastEpisodeSaver $podcasSaver,
        private LinkContentSaver $linkSaver,
    ) {
    }

    public function setSaver(string $option): self  {
        $this->option = $option;
        return $this;
    }

    public function getSaverClass(): SaverInterface {
        return match ($this->option) {
            'link' => $this->linkSaver,
            'podcast' => $this->podcasSaver,
        };
    }
}
