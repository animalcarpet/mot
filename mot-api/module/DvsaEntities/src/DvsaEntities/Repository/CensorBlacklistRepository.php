<?php

namespace DvsaEntities\Repository;

use Doctrine\ORM\EntityRepository;
use DvsaEntities\Entity\CensorBlacklist;

/**
 * Class CensorBlacklistRepository
 * @package DvsaEntities\Repository
 * @codeCoverageIgnore
 */
class CensorBlacklistRepository extends EntityRepository
{
    /**
     * @return CensorBlacklist[]
     */
    public function getBlacklist()
    {
        return $this->findAll();
    }
}
