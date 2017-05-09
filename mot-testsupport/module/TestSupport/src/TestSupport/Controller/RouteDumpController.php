<?php

namespace TestSupport\Controller;

use TestSupport\Helper\TestDataResponseHelper;
use Zend\Mvc\Controller\AbstractRestfulController;

/**
 * Returns a JSON of all the routes for security, etc, purposes.
 */
class RouteDumpController extends AbstractRestfulController
{
    public function getList()
    {
        return TestDataResponseHelper::jsonOk(['routes' => $this->getServiceLocator()->get('config')['router']]);
    }
}
