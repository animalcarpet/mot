<?php

namespace DvsaMotApiTest\Service\InMemoryRepositories;

use Doctrine\ORM\EntityNotFoundException;
use Doctrine\ORM\EntityRepository;
use DvsaEntities\Entity\ReasonForRejectionType;
use DvsaEntities\Repository\ReasonForRejectionTypeRepositoryInterface;

class InMemoryReasonForRejectionTypeRepository
    implements ReasonForRejectionTypeRepositoryInterface
{
    /** @var ReasonForRejectionType[] */
    private $entityStore;

    /** @var ReasonForRejectionType */
    private $lastTypeQueried;

    /**
     * InMemoryReasonForRejectionTypeRepository constructor.
     *
     * @param ReasonForRejectionType[] $entities
     */
    public function __construct(array $entities)
    {
        $this->entityStore = $entities;
    }

    /**
     * @param $rfrType
     * @return ReasonForRejectionType
     * @throws EntityNotFoundException
     */
    public function getByType($rfrType)
    {
        $this->lastTypeQueried = $rfrType;

        foreach ($this->entityStore as $entity) {
            if ($entity->getReasonForRejectionType() === $rfrType) {
                return $entity;
            }
        }

        throw new EntityNotFoundException();
    }

    public function getLastTypeQueried()
    {
        return $this->lastTypeQueried;
    }
}