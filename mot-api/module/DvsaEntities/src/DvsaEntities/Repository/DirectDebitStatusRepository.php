<?php

namespace DvsaEntities\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Direct debit status repository.
 */
class DirectDebitStatusRepository extends EntityRepository
{
    use EnumType1RepositoryTrait;
}
