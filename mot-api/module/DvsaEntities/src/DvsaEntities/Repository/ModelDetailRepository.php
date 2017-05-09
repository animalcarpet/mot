<?php

namespace DvsaEntities\Repository;

use DvsaCommonApi\Service\Exception\NotFoundException;
use DvsaEntities\Entity\ModelDetail;

/**
 * Class ModelDetailRepository.
 *
 * @codeCoverageIgnore
 */
class ModelDetailRepository extends AbstractMutableRepository
{
    public function getAll()
    {
        return $this->findAll();
    }

    /**
     * @param $id
     *
     * @return ModelDetail
     *
     * @throws \DvsaCommonApi\Service\Exception\NotFoundException
     */
    public function get($id)
    {
        $entity = $this->find($id);
        if (!$entity) {
            throw new NotFoundException($this->getEntityName(), $id);
        }

        return $entity;
    }

    /**
     * @param string $makeCode
     * @param string $modelCode
     *
     * @return ModelDetail[]
     */
    public function getByModel($makeCode, $modelCode)
    {
        $qb = $this
            ->createQueryBuilder('md')
            ->innerJoin('md.make', 'mk')
            ->innerJoin('md.model', 'ml')
            ->where('mk.code = :MAKE_CODE')
            ->andWhere('ml.code = :MODEL_CODE')
            ->setParameter('MAKE_CODE', $makeCode)
            ->setParameter('MODEL_CODE', $modelCode);

        return $qb->getQuery()->getResult();
    }
}
