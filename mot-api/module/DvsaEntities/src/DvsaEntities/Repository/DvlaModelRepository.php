<?php

namespace DvsaEntities\Repository;

use DvsaCommonApi\Service\Exception\NotFoundException;
use DvsaEntities\Entity\DvlaModel;

/**
 * Class DvlaModelRepository.
 *
 * @codeCoverageIgnore
 */
class DvlaModelRepository extends AbstractMutableRepository
{
    public function getAll()
    {
        return $this->findAll();
    }

    /**
     * @param int $id
     *
     * @return DvlaModel
     *
     * @throws \DvsaCommonApi\Service\Exception\NotFoundException
     */
    public function get($id)
    {
        $result = $this->find($id);
        if (empty($result)) {
            throw new NotFoundException('DvlaModel', $id);
        }

        return $result;
    }

    /**
     * @param string $code
     *
     * @return DvlaModel
     *
     * @throws NotFoundException
     */
    public function getByCode($code)
    {
        $result = $this->findOneBy(['code' => $code]);
        if (is_null($result)) {
            throw new NotFoundException('DvlaModel', $code);
        }

        return $result;
    }

    /**
     * @param $make_code
     * @param $model_code
     *
     * @return DvlaModel|null
     */
    public function findByMakeCodeModelCode($make_code, $model_code)
    {
        $qb = $this->createQueryBuilder('m');

        $qb
            ->where('m.make_code = :make_code')
            ->andWhere('m.code = :model_code')
            ->setParameter(':make_code', $make_code)
            ->setParameter(':model_code', $model_code)
            ->setMaxResults(1);

        $resultArray = $qb->getQuery()->getResult();

        return empty($resultArray) ? null : $resultArray[0];
    }
}
