<?php

namespace DvsaEntities\Repository;

use Doctrine\DBAL\LockMode;
use Doctrine\ORM\Tools\Pagination\Paginator;
use DvsaEntities\Entity\Vehicle;

/**
 * Class VehicleRepository.
 *
 * @method Vehicle get(int $id)
 * @method Vehicle|null find($id, $lockMode = LockMode::NONE, $lockVersion = null)
 * @codeCoverageIgnore
 */
class VehicleRepository extends AbstractVehicleRepository
{
    public function getAll()
    {
        return $this->findAll();
    }

    /**
     * This function allow us to paginate all the database to avoid memory limit.
     *
     * @param int    $start
     * @param int    $offset
     * @param string $orderBy
     * @param int    $hydrateMode
     *
     * @return array
     */
    public function getAllDataForEsIngestion($start, $offset, $orderBy = 'vehicle.id', $hydrateMode = \Doctrine\ORM\Query::HYDRATE_OBJECT)
    {
        $qb = $this
            ->createQueryBuilder('vehicle')
            ->orderBy($orderBy);

        $paginate = new Paginator($qb, $fetchJoinCollection = true);
        $paginate
            ->getQuery()
            ->setFirstResult($start)
            ->setMaxResults($offset)
            ->setHydrationMode($hydrateMode);

        return $paginate;
    }

    /**
     * Search used for 'vehicle information' list.
     *
     * @param string $vin   VIN number
     * @param string $reg   Registration number
     * @param int    $limit
     *
     * @return array
     */
    public function search($vin, $reg, $limit = null)
    {
        return $this->createExactMatchSearchQueryBuilder('vehicle', $vin, $reg, $limit, 'vin', 'registration')
            ->select([
                'vehicle',
            ])
            ->getQuery()
            ->getResult();
    }

    /**
     * Different conditions are used on 'vehicle information' search.
     *
     * @param $alias
     * @param $vin
     * @param $reg
     * @param $limit
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function createExactMatchSearchQueryBuilder($alias, $vin, $reg, $limit, $vinColumn = 'vinCollapsed',
                                                          $registrationColumn = 'registrationCollapsed')
    {
        $queryBuilder = $this->createQueryBuilder($alias);

        if (!empty($vin)) {
            $preparedVin = $this->sanitize($vin);
            $queryBuilder->andWhere("vehicle.{$vinColumn} = :vin");
            $queryBuilder->setParameter('vin', $preparedVin);
        }

        if (!empty($reg)) {
            $queryBuilder->andWhere("vehicle.{$registrationColumn} = :reg");
            $queryBuilder->setParameter('reg', $this->sanitize($reg));
        }

        if (!empty($limit)) {
            $queryBuilder->setMaxResults($limit);
        }

        return $queryBuilder;
    }
}
