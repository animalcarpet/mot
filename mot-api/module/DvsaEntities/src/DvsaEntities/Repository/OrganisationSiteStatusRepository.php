<?php

namespace DvsaEntities\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Repository for.
 *
 * @see \DvsaEntities\Entity\OrganisationSiteStatus
 * @codeCoverageIgnore
 */
class OrganisationSiteStatusRepository extends EntityRepository
{
    use EnumType1RepositoryTrait;
}
