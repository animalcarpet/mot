<?php

namespace DvsaEntities\Repository;

use Doctrine\ORM\EntityRepository;
use DvsaEntities\DqlBuilder\TesterSearchParamDqlBuilder;

/**
 * TesterSearchRepository.
 *
 * Custom Doctrine Repository for reusable DQL queries
 *
 * @codeCoverageIgnore
 */
class TesterSearchRepository extends EntityRepository
{
    use SearchRepositoryTrait;

    public function getSqlBuilder($params)
    {
        return new TesterSearchParamDqlBuilder(
            $this->getEntityManager(),
            $params
        );
    }
}
