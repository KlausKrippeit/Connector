<?php

#[ORM\Entity]
#[ORM\Table(name: 'podcast_episode')]
#[ORM\UniqueConstraint(name: 'uniq_guid', columns: ['guid'])]
class PodcastEpisodeEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    public int $id;

    #[ORM\Column(length: 64)]
    public string $guid;

    #[ORM\Column(length: 255)]
    public string $title;

    #[ORM\Column(type: 'text')]
    public string $description;

    #[ORM\Column(type: 'datetime_immutable')]
    public \DateTimeImmutable $publishedAt;

    #[ORM\Column(length: 255)]
    public string $episodeUrl;

    #[ORM\Column(length: 255)]
    public string $audioUrl;

    #[ORM\Column(type: 'bigint')]
    public int $audioSize;

    #[ORM\Column(length: 64)]
    public string $audioType;

    #[ORM\Column(length: 255, nullable: true)]
    public ?string $localPath = null;

    #[ORM\Column(length: 20)]
    public string $status = 'new';
}
