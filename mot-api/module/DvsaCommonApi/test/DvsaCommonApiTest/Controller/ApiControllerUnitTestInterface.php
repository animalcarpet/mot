<?php

namespace DvsaCommonApiTest\Controller;

/**
 * Interface for ApiControllerUnitTestTrait
 * Should be used together.
 */
interface ApiControllerUnitTestInterface
{
    const OK = 200;
    const NOT_ALLOWED = 405;

    public function mockServices();
}
