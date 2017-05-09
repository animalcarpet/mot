<?php

namespace DvsaEntities\Repository;

use Doctrine\ORM\QueryBuilder;
use DvsaCommonApi\Service\Exception\NotFoundException;

/**
 * Class AbstractVehicleRepository.
 *
 * @codeCoverageIgnore
 */
abstract class AbstractVehicleRepository extends AbstractMutableRepository
{
    /**
     * @param $id
     *
     * @throws \DvsaCommonApi\Service\Exception\NotFoundException
     *
     * @return null|object
     */
    public function get($id)
    {
        $vehicle = $this->find($id);
        if ($vehicle === null) {
            throw new NotFoundException($this->getClassName(), $id);
        }

        return $vehicle;
    }

    /**
     * @param string $vin       VIN number
     * @param string $reg       Registration number
     * @param bool   $isFullVin Indicates whether passed VIN number is full
     * @param int    $limit
     *
     * @return array
     */
    public function searchVehicle($vin, $reg, $isFullVin, $limit = null)
    {
        return $this->createSearchQueryBuilder('vehicle', $vin, $reg, $isFullVin, $limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Sanitize vin or reg.
     *
     * @param $string
     *
     * @return string
     */
    protected function sanitize($string)
    {
        return strtoupper($string);
    }

    /**
     * @param string $alias
     * @param string $vin       VIN number
     * @param string $reg       Registration number
     * @param bool   $isFullVin Indicates whether passed VIN number is full
     * @param int    $limit
     *
     * @return QueryBuilder
     */
    protected function createSearchQueryBuilder($alias, $vin, $reg, $isFullVin, $limit = null)
    {
        $queryBuilder = $this->createQueryBuilder($alias);

        $preparedVin = $this->sanitize($vin);
        $preparedReg = $this->sanitize($reg);
        $isVinEmpty = is_null($vin) || $vin === '';
        $isRegEmpty = is_null($reg) || $reg === '';

        if ($isVinEmpty) {
            $queryBuilder->andWhere("vehicle.{$this->getVinColumn()} IS NULL");
        } else {
            if ($isFullVin || $isRegEmpty) {
                $queryBuilder->andWhere("vehicle.{$this->getVinColumn()} = :vin");
                $queryBuilder->setParameter('vin', $preparedVin);
            } else {
                $queryBuilder->andWhere("vehicle.{$this->getVinColumn()} LIKE :partialVin");
                $queryBuilder->setParameter('partialVin', '%'.$preparedVin);
            }
        }

        if ($isRegEmpty) {
            $queryBuilder->andWhere("vehicle.{$this->getRegistrationColumn()} IS NULL");
        } else {
            $queryBuilder->andWhere("vehicle.{$this->getRegistrationColumn()} = :reg");
            $queryBuilder->setParameter('reg', $preparedReg);
        }

        if (is_int($limit)) {
            $queryBuilder->setMaxResults($limit);
        }

        return $queryBuilder;
    }

    /**
     * @return string
     */
    protected function getVinColumn()
    {
        return 'vinCollapsed';
    }

    /**
     * @return string
     */
    protected function getRegistrationColumn()
    {
        return 'registrationCollapsed';
    }
}
