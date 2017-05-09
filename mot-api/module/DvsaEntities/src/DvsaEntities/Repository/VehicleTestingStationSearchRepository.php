<?php

namespace DvsaEntities\Repository;

use Doctrine\ORM\EntityRepository;
use DvsaEntities\DqlBuilder\SiteSearchParamDqlBuilder;

/**
 * VehicleTestingStationRepository.
 *
 * Custom Doctrine Repository for reusable DQL queries
 *
 * @codeCoverageIgnore
 */
class VehicleTestingStationSearchRepository extends EntityRepository
{
    use SearchRepositoryTrait;

    public function getSqlBuilder($params)
    {
        return new SiteSearchParamDqlBuilder(
            $this->getEntityManager(),
            $params
        );
    }
}
