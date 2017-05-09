<?php

namespace DvsaEntities\Repository;

use DvsaCommonApi\Service\Exception\NotFoundException;
use DvsaEntities\Entity\Make;

/**
 * Class MakeRepository.
 *
 * @codeCoverageIgnore
 */
class MakeRepository extends AbstractMutableRepository
{
    public function getAll()
    {
        return $this->findAll();
    }

    /**
     * @param int $id
     *
     * @return Make
     *
     * @throws \DvsaCommonApi\Service\Exception\NotFoundException
     */
    public function get($id)
    {
        $result = $this->find($id);
        if (empty($result)) {
            throw new NotFoundException('Make', $id);
        }

        return $result;
    }

    /**
     * @param string $code
     *
     * @return Make
     *
     * @throws NotFoundException
     */
    public function getByCode($code)
    {
        $result = $this->findOneBy(['code' => $code]);
        if (is_null($result)) {
            throw new NotFoundException('Make', $code);
        }

        return $result;
    }

    /**
     * @param $name
     *
     * @return array
     */
    public function findByName($name)
    {
        $qb = $this->createQueryBuilder('m');
        $qb
            ->where($qb->expr()->like('m.name', ':name'))
            ->andWhere($qb->expr()->eq('m.isVerified', 1))
            ->andWhere($qb->expr()->eq('m.isSelectable', 1))
            ->orderBy('m.name', 'ASC')
            ->setParameter(':name', '%'.$name.'%');

        return $qb->getQuery()->getResult();
    }
}
