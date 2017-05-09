<?php

namespace DvsaEntities\Repository;

use DvsaEntities\Entity\OrganisationPositionHistory;

/**
 * Class OrganisationPositionHistoryRepository.
 *
 * @codeCoverageIgnore
 */
class OrganisationPositionHistoryRepository extends AbstractMutableRepository
{
    /**
     * @param $position OrganisationPositionHistory
     */
    public function persist($position)
    {
        parent::persist($position);
    }
}
