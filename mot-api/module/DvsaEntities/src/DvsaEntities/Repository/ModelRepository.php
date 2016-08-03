<?php

namespace DvsaEntities\Repository;

use DvsaCommonApi\Service\Exception\NotFoundException;
use DvsaEntities\Entity\Model;

/**
 * Class ModelRepository
 * @package DvsaEntities\Repository
 * @codeCoverageIgnore
 */
class ModelRepository extends AbstractMutableRepository
{

    public function getAll()
    {
        return $this->findAll();
    }

    /**
     * @param $id
     *
     * @return Model
     * @throws \DvsaCommonApi\Service\Exception\NotFoundException
     */
    public function get($id)
    {
        $result = $this->find($id);
        if (empty($result)) {
            throw new NotFoundException("Model", $id);
        }
        return $result;
    }

    /**
     * @param string $makeId
     * @param string $modelCode
     * @return Model
     * @throws NotFoundException
     */
    public function getByCode($makeId, $modelCode)
    {
        $result = $this->findOneBy(['makeId' => $makeId, 'code' => $modelCode]);
        if (is_null($result)) {
            throw new NotFoundException("Model", $makeId . '/' . $modelCode);
        }
        return $result;
    }

    /**
     * @param string $makeCode
     * @param string $modelCode
     * @return Model|null
     */
    public function findOneByMakeAndModel($makeCode, $modelCode)
    {
        $query = $this
            ->createQueryBuilder("model")
            ->addSelect(["make"])
            ->innerJoin("model.make", "make")
            ->where("make.code = :makeCode")
            ->andWhere("model.code = :modelCode")
            ->setParameter('makeCode', $makeCode)
            ->setParameter('modelCode', $modelCode)
            ->getQuery();

        return $query->getOneOrNullResult();
    }

    /**
     * @param string $makeCode
     *
     * @return Model[]
     * @throws \DvsaCommonApi\Service\Exception\NotFoundException
     */
    public function getByMake($makeCode)
    {
        $result = $this->findBy(['makeCode' => $makeCode], ['name' => 'ASC']);
        if (empty($result)) {
            throw new NotFoundException("Model (by make)", $makeCode);
        }
        return $result;
    }

    /**
     * @param string $makeId
     *
     * @return Model[]
     * @throws \DvsaCommonApi\Service\Exception\NotFoundException
     */
    public function getByMakeId($makeId)
    {
        $result = $this->findBy(
            [
                'makeId' => $makeId,
                'isVerified' => 1,
            ],
            ['name' => 'ASC']
        );

        if (empty($result)) {
            throw new NotFoundException("Model (by make id)", $makeId);
        }
        return $result;
    }

    /**
     * @param $name
     * @param $makeId
     *
     * @return array
     */
    public function findByNameForMake($name, $makeId)
    {
        $qb = $this->createQueryBuilder("m");
        $qb
            ->where($qb->expr()->eq("m.makeId", ":makeId"))
            ->andWhere($qb->expr()->like("m.name", ":name"))
            ->andWhere($qb->expr()->eq('m.isVerified', 1))
            ->setParameter(":makeId", $makeId)
            ->setParameter(":name", '%' . $name . '%')
        ;

        return $qb->getQuery()->getResult();
    }
}
