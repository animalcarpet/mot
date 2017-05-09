<?php

namespace SiteApi\Factory;

use DvsaEntities\Entity\Site;
use SiteApi\Model\SitePersonnel;

/**
 * Class SitePersonnelFactory.
 */
class SitePersonnelFactory
{
    public function create(Site $site)
    {
        $positions = $site->getPositions()->toArray();

        return new SitePersonnel($site, $positions);
    }
}
