<?php

namespace DvsaMotApiTest\Controller;

use DvsaMotApi\Controller\IndexController;

/**
 * Class IndexControllerTest.
 */
class IndexControllerTest extends AbstractMotApiControllerTestCase
{
    protected function setUp()
    {
        $this->controller = new IndexController();
        parent::setUp();
    }

    public function testGetListCanBeAccessed()
    {
        $result = $this->controller->dispatch($this->request);
        $response = $this->controller->getResponse();

        $this->assertEquals(200, $response->getStatusCode());
    }
}
