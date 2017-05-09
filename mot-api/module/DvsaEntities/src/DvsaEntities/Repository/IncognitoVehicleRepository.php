<?php

namespace DvsaEntities\Repository;

use DvsaCommonApi\Service\Exception\NotFoundException;
use DvsaEntities\Entity\IncognitoVehicle;
use DvsaEntities\Entity\Vehicle;

/**
 * Class IncognitoVehicleRepository.
 */
class IncognitoVehicleRepository extends AbstractMutableRepository
{
    /**
     * @param $id
     *
     * @return IncognitoVehicle
     *
     * @throws NotFoundException
     */
    public function get($id)
    {
        $result = $this->find($id);
        if (empty($result)) {
            throw new NotFoundException('Incognito Vehicle', $id);
        }

        return $result;
    }

    /**
     * @param Vehicle $vehicle
     *
     * @return array
     */
    public function findAllCampaignsForVehicle(Vehicle $vehicle)
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();
        $queryBuilder
            ->select('incognitoVehicle')
            ->from(IncognitoVehicle::class, 'incognitoVehicle')
            ->where('incognitoVehicle.vehicle = :vehicle')
            ->setParameter('vehicle', $vehicle);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * @param Vehicle $vehicle
     * @param int     $campaignId
     *
     * @return array|null
     */
    public function findAllCampaignsForVehicleExcept(Vehicle $vehicle, $campaignId)
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();
        $queryBuilder
            ->select('incognitoVehicle')
            ->from(IncognitoVehicle::class, 'incognitoVehicle')
            ->where('incognitoVehicle.vehicle = :vehicle')
            ->andWhere('id <> :campaignId')
            ->setParameter('vehicle', $vehicle)
            ->setParameter('campaignId', $campaignId);

        $result = $queryBuilder->getQuery()->getResult();

        return empty($result) ? null : $result;
    }

    /**
     * @param Vehicle $vehicle
     *
     * @return IncognitoVehicle|null
     */
    public function getCurrent(Vehicle $vehicle)
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();
        $queryBuilder
            ->select('incognitoVehicle')
            ->from(IncognitoVehicle::class, 'incognitoVehicle')
            ->where('incognitoVehicle.vehicle = :vehicle')
            ->andWhere('incognitoVehicle.startDate < :now')
            ->andWhere('incognitoVehicle.endDate > :now')
            ->setParameter('vehicle', $vehicle)
            ->setParameter('now', new \DateTime('NOW'));

        $result = $queryBuilder->getQuery()->getResult();

        return empty($result) ? null : $result[0];
    }
}
