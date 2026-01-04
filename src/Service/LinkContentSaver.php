<?php

namespace Connector\Service;

use Connector\DTO\LinkContent;
use Connector\Entity\LinkContentEntity;
use Connector\Repository\LinkContentEntityRepository;
use Doctrine\ORM\EntityManagerInterface;

class LinkContentSaver implements SaverInterface
{
    public function __construct(
        private LinkContentEntityRepository $linkContentRepository,
        private EntityManagerInterface $em
    ) {}

    public function save(LinkContent $link): LinkContentEntity
    {
        // Neue Episode anlegen
        $entity = new LinkContentEntity();
        $entity->setGuid($link->guid);
        $entity->setTitle($link->title);
        $entity->setDescription($link->description);
        $entity->setPublishedAt($link->publishedAt);
        $entity->setFileUrl($link->fileUrl);
        $entity->setFileSize($link->fileSize);
        $entity->setFileType($link->fileType);
        $entity->setStatus($link->status);
        $entity->setSource($link->source);

        $this->em->persist($entity);
        $this->em->flush();

        return $entity;
    }
}
