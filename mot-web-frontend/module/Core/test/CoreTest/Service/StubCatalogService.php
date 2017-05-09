<?php

namespace CoreTest\Service;

use Application\Service\CatalogService;

/**
 * Stub catalog service for use in test code.
 */
class StubCatalogService extends CatalogService
{
    public function __construct()
    {
        parent::__construct(
            new \Zend\Cache\Storage\Adapter\Memory(),
            new StubRestForCatalog()
        );
    }
}
