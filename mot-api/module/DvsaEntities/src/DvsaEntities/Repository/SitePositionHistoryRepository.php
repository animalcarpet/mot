<?php

namespace DvsaEntities\Repository;

use DvsaEntities\Entity\SitePositionHistory;

/**
 * Class SitePositionHistoryRepository.
 *
 * @codeCoverageIgnore
 */
class SitePositionHistoryRepository extends AbstractMutableRepository
{
    /**
     * @param $position SitePositionHistory
     */
    public function persist($position)
    {
        parent::persist($position);
    }
}
