<?php

namespace DvsaEntities\Repository;

use Doctrine\ORM\EntityRepository;
use DvsaEntities\Entity\CensorBlacklist;

/**
 * Class CensorBlacklistRepository.
 *
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
