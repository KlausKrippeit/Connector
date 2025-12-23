<?php

namespace Connector\Entity;

use Connector\Repository\PodcastEpisodeEntityRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PodcastEpisodeEntityRepository::class)]
#[ORM\Table(name: 'podcast_episode')]
#[ORM\UniqueConstraint(name: 'uniq_guid', columns: ['guid'])]
class PodcastEpisodeEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 64)]
    private ?string $guid = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $description = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $publishedAt = null;

    #[ORM\Column(length: 255)]
    private ?string $episodeUrl = null;

    #[ORM\Column(length: 255)]
    private ?string $audioUrl = null;

    #[ORM\Column(type: Types::BIGINT)]
    private ?string $audioSize = null;

    #[ORM\Column(length: 64)]
    private ?string $audioType = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $localPath = null;

    #[ORM\Column(length: 20)]
    private ?string $status = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getGuid(): ?string
    {
        return $this->guid;
    }

    public function setGuid(string $guid): static
    {
        $this->guid = $guid;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getPublishedAt(): ?\DateTimeImmutable
    {
        return $this->publishedAt;
    }

    public function setPublishedAt(\DateTimeImmutable $publishedAt): static
    {
        $this->publishedAt = $publishedAt;

        return $this;
    }

    public function getEpisodeUrl(): ?string
    {
        return $this->episodeUrl;
    }

    public function setEpisodeUrl(string $episodeUrl): static
    {
        $this->episodeUrl = $episodeUrl;

        return $this;
    }

    public function getAudioUrl(): ?string
    {
        return $this->audioUrl;
    }

    public function setAudioUrl(string $audioUrl): static
    {
        $this->audioUrl = $audioUrl;

        return $this;
    }

    public function getAudioSize(): ?string
    {
        return $this->audioSize;
    }

    public function setAudioSize(string $audioSize): static
    {
        $this->audioSize = $audioSize;

        return $this;
    }

    public function getAudioType(): ?string
    {
        return $this->audioType;
    }

    public function setAudioType(string $audioType): static
    {
        $this->audioType = $audioType;

        return $this;
    }

    public function getLocalPath(): ?string
    {
        return $this->localPath;
    }

    public function setLocalPath(?string $localPath): static
    {
        $this->localPath = $localPath;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }
}
