<?php

namespace DvsaEntities\Repository\InMemory;

use Doctrine\ORM\EntityNotFoundException;
use Doctrine\ORM\EntityRepository;
use DvsaEntities\Entity\MotTestSurveyResult;
use DvsaEntities\Repository\MotTestSurveyResultRepository;

class InMemoryMotTestSurveyResultRepository extends EntityRepository implements MotTestSurveyResultRepository
{
    /**
     * @var MotTestSurveyResult[]
     */
    private $entityStore;

    public function __construct()
    {
        parent::__construct($this->_em, InMemoryMotTestSurveyResultRepository::class);
        $this->entityStore = [];
    }

    /**
     * @param int|string $motTestId
     * @return MotTestSurveyResult
     * @throws EntityNotFoundException
     */
    public function findByMotTestId($motTestId)
    {
        foreach ($this->entityStore as $entity) {
            if ($entity->getMotTest()->getId() === $motTestId) {
                return $entity;
            }
        }

        throw new EntityNotFoundException();
    }
}
