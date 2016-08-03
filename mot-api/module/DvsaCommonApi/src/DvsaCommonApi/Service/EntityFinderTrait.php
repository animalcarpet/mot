<?php

namespace DvsaCommonApi\Service;

use Doctrine\ORM\EntityManager;
use DvsaCommonApi\Service\Exception\NotFoundException;
use DvsaEntities\Entity\Person;

/**
 * Find entity by id/criteria or throw NotFoundException
 * @deprecated use PersonRepository
 */
trait EntityFinderTrait
{
    /** @var EntityManager $entityManager */
    protected $entityManager;

    /**
     * @param string $entityClass
     * @param int    $id
     * @param string $entitySimpleName
     *
     * @throws \DvsaCommonApi\Service\Exception\NotFoundException
     *
     * @return object
     */
    public function findOrThrowException($entityClass, $id, $entitySimpleName)
    {
        $result = null;

        if ($id) {
            $result = $this->entityManager->find($entityClass, $id);
        }

        if (!$result) {
            throw new NotFoundException($entitySimpleName, $id);
        }

        return $result;
    }

    /**
     * @param string $entityClassPath
     * @param array  $criteria
     *
     * @throws \DvsaCommonApi\Service\Exception\NotFoundException
     *
     * @return object
     */
    public function findOneByOrThrowException($entityClassPath, $criteria)
    {
        $entityRepository = $this->entityManager->getRepository($entityClassPath);

        $entity = $entityRepository->findOneBy($criteria);

        if (null === $entity) {
            throw new NotFoundException($entityClassPath);
        }

        return $entity;
    }

    /**
     * @param string $entityClassPath
     * @param array  $criteria
     * @param array  $sortBy
     *
     * @return object
     */
    public function findAllByOrThrowException($entityClassPath, $criteria, $sortBy = null)
    {
        $result = $this->entityManager->getRepository($entityClassPath)->findBy($criteria, $sortBy);

        return $result;
    }

    /**
     * @return EntityHelperService
     */
    public function getEntityHelper()
    {
        return new EntityHelperService($this->entityManager);
    }

    /**
     * @return EntityManager
     */
    public function getEntityManager()
    {
        return $this->entityManager;
    }

    /**
     * @param int $personId
     *
     * @return Person
     */
    public function findPerson($personId)
    {
        return $this->findOrThrowException(Person::class, $personId, Person::ENTITY_NAME);
    }
}
