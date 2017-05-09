<?php

namespace DvsaMotApi\Controller;

use DvsaCommonApi\Model\ApiResponse;
use DvsaCommonApi\Controller\AbstractDvsaRestfulController;

/**
 * Class IndexController.
 */
class IndexController extends AbstractDvsaRestfulController
{
    public function getList()
    {
        $this->getLogger()->debug('Index of MOT API');

        return ApiResponse::jsonOk('Welcome to DVSA-MOT Rest Api.');
    }
}
