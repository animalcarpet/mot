<?php

namespace DvsaEntitiesTest\DqlBuilder;

use DvsaEntities\DqlBuilder\SearchParam\MotTestSearchParam;

trait BuildMotTestSearchParamsTrait
{
    /**
     * Utility function to build Mot Test search param with a site number.
     *
     * @param $mockEm
     * @param $siteNumber
     *
     * @return MotTestSearchParam
     */
    protected function buildMotTestSearchParamWithSiteNumber($mockEm, $siteNumber)
    {
        return (new MotTestSearchParam($mockEm))
            ->setSiteNumber($siteNumber)
            ->process();
    }

    /**
     * Utility function to build Mot Test search param with a tester id.
     *
     * @param $mockEm
     * @param $id
     *
     * @return MotTestSearchParam
     */
    protected function buildMotTestSearchParamWithTesterId($mockEm, $id)
    {
        return (new MotTestSearchParam($mockEm))
            ->setTesterId($id)
            ->process();
    }
}
