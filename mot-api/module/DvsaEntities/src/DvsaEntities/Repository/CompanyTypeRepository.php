<?php

namespace DvsaEntities\Repository;

use DvsaEntities\Entity\CompanyType;

/**
 * Repository for {@link CompanyType}.
 *
 * @method CompanyType|null findOneByName(string $name)
 * @codeCoverageIgnore
 */
class CompanyTypeRepository extends AbstractMutableRepository
{
    use EnumType1RepositoryTrait;
}
