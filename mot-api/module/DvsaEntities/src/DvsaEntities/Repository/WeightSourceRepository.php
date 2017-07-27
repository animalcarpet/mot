<?php

namespace DvsaEntities\Repository;

use DvsaEntities\Entity\WeightSource;

/**
 * @method WeightSource getByCode($code)
 * Repository for weight source.
 */
class WeightSourceRepository extends AbstractMutableRepository
{
    use EnumType1RepositoryTrait;
}
