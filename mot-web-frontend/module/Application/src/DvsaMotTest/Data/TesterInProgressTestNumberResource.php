<?php

namespace DvsaMotTest\Data;

use Application\Data\ApiResources;
use DvsaCommon\UrlBuilder\TesterUrlBuilder;

/**
 * Handles calls to API for test.
 */
class TesterInProgressTestNumberResource extends ApiResources
{
    /**
     * @param int $personId
     *
     * @return int|null test id if tester has test in progress
     */
    public function get($personId)
    {
        $path = TesterUrlBuilder::create()->testerInProgressTestNumber($personId)->toString();

        return $this->restGet($path)['data'];
    }
}
