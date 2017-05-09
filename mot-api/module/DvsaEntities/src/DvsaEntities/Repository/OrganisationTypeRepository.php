<?php

namespace DvsaEntities\Repository;

use DvsaEntities\Entity\OrganisationType;

/**
 * Repository for {@link OrganisationType}.
 *
 * @method OrganisationType|null findOneByName(string $name)
 * @codeCoverageIgnore
 */
class OrganisationTypeRepository extends AbstractMutableRepository
{
    use EnumType1RepositoryTrait;
}
