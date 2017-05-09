<?php

namespace TestSupport\Controller;

use TestSupport\Helper\TestDataResponseHelper;
use Zend\Mvc\Controller\AbstractRestfulController;

/**
 * Class IndexController.
 */
class IndexController extends AbstractRestfulController
{
    public function getList()
    {
        return TestDataResponseHelper::jsonOk(
            [
                'message' => 'Welcome to the DVSA-MOT Test Data API',
                'link' => 'https://wiki.i-env.net/display/MP/Creating+Demo+Data',
            ]
        );
    }
}
