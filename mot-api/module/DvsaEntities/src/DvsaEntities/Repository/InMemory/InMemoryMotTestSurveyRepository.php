<?php

namespace DvsaEntities\Repository\InMemory;

use Doctrine\ORM\EntityNotFoundException;
use Doctrine\ORM\EntityRepository;
use DvsaEntities\Entity\MotTest;
use DvsaEntities\Entity\MotTestSurvey;
use DvsaEntities\Repository\MotTestSurveyRepository;

class InMemoryMotTestSurveyRepository extends EntityRepository implements MotTestSurveyRepository
{
    /**
     * @var MotTestSurvey[]
     */
    private $entityStore;

    /**
     * InMemoryMotTestSurveyRepository constructor.
     *
     * @param MotTestSurvey[] $entities
     */
    public function __construct(array $entities)
    {
        parent::__construct($this->_em, InMemoryMotTestSurveyRepository::class);

        $this->entityStore = $entities;
    }

    /**
     * @param string $token
     *
     * @throws EntityNotFoundException
     *
     * @return MotTestSurvey
     */
    public function findOneByToken($token)
    {
        foreach ($this->entityStore as $entity) {
            if ($entity->getToken() === $token) {
                return $entity;
            }
        }

        throw new EntityNotFoundException();
    }

    /**
     * @param string $userId
     *
     * @throws EntityNotFoundException
     *
     * @return string
     */
    public function getLastUserSurveyDate($userId)
    {
        /** @var MotTestSurvey $match */
        $match = null;
        foreach ($this->entityStore as $entity) {
            if ($entity->getCreatedBy()->getId() == $userId &&
                ($match === null || $entity->getCreatedOn() > $match->getCreatedOn())
            ) {
                $match = $entity;
            }
        }

        if ($match !== null) {
            return $match->getCreatedOn()->format('Y-m-d H:i:s');
        }
        throw new EntityNotFoundException();
    }

    /**
     * @return int
     */
    public function getLastSurveyMotTestId()
    {
        $this->getLastUserMotTestSurvey()->getMotTest()->getId();
    }

    /**
     * @throws EntityNotFoundException
     *
     * @return MotTestSurvey
     */
    private function getLastUserMotTestSurvey()
    {
        /** @var MotTestSurvey $match */
        $match = null;
        foreach ($this->entityStore as $entity) {
            if ($match === null || $entity->getCreatedOn() > $match->getCreatedOn()) {
                $match = $entity;
            }
        }

        if (null !== $match) {
            return $match;
        }
        throw new EntityNotFoundException();
    }
}
