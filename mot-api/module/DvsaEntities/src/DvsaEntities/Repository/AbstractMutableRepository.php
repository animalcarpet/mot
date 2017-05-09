<?php

namespace DvsaEntities\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * An abstract repository as a unified approach to creating repositories capable of doing mutable operations.
 *
 * Class AbstractMutableRepository
 *
 * @codeCoverageIgnore
 */
abstract class AbstractMutableRepository extends EntityRepository
{
    /**
     * Persist an entity.
     */
    public function persist($entity)
    {
        $this->getEntityManager()->persist($entity);
    }

    /**
     * Persists and flushes an entity.
     *
     * @param $entity
     */
    public function save($entity)
    {
        $this->persist($entity);
        $this->flush($entity);
    }

    /**
     * Removes an entity.
     *
     * @param $entity
     */
    public function remove($entity)
    {
        $this->getEntityManager()->remove($entity);
    }

    /**
     * @param $entity
     */
    public function flush($entity = null)
    {
        $this->getEntityManager()->flush($entity);
    }

    /**
     * @param $id
     *
     * @return bool|\Doctrine\Common\Proxy\Proxy|null|object
     */
    public function getReference($id)
    {
        return $this->getEntityManager()->getReference($this->getClassName(), $id);
    }
}
