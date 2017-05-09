<?php

namespace DvsaEntities\Repository;

use DvsaEntities\Entity\DvlaVehicle;
use DvsaEntities\Entity\Vehicle;

/**
 * Class DvlaVehicleRepository.
 *
 * @method DvlaVehicle get(int $id)
 * @codeCoverageIgnore
 */
class DvlaVehicleRepository extends AbstractVehicleRepository
{
    /**
     * @param $dvlaVehicleId
     *
     * @return int|null
     *
     * The vin and reg must match, because they could have been changed since via Certificate Replacement.
     * In that case it may not be considered the same vehicle
     */
    public function findMatchingDvsaVehicleIdForDvlaVehicle($dvlaVehicleId)
    {
        $dqlBuilder = $this->getEntityManager()->createQueryBuilder();

        $dqlBuilder->select('vehicle.id')
            ->from(DvlaVehicle::class, 'dvla_vehicle')
            ->innerJoin(Vehicle::class, 'vehicle', 'WITH', 'dvla_vehicle.vehicleId = vehicle.id')
            ->where('dvla_vehicle.registration = vehicle.registration')
            ->andWhere('dvla_vehicle.vin = vehicle.vin')
            ->andWhere('dvla_vehicle.id = :id')
            ->setParameter('id', $dvlaVehicleId);

        $result = $dqlBuilder->getQuery()->getResult();

        return $result[0]['id'];
    }
}
