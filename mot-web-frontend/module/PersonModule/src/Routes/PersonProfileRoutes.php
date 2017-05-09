<?php

namespace Dvsa\Mot\Frontend\PersonModule\Routes;

use Dvsa\Mot\Frontend\PersonModule\View\ContextProvider;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;

class PersonProfileRoutes implements AutoWireableInterface
{
    const TEST_LOGS = '/tester-mot-test-log';
    const TEST_QUALITY = '/test-quality-information';
    const TEST_QUALITY_INFORMATION = '/test-quality-information';
    private $contextProvider;

    public function __construct(ContextProvider $contextProvider)
    {
        $this->contextProvider = $contextProvider;
    }

    public function getRoute()
    {
        $context = $this->contextProvider->getContext();
        switch ($context) {
            case ContextProvider::YOUR_PROFILE_CONTEXT:
                return ContextProvider::YOUR_PROFILE_PARENT_ROUTE;
            case ContextProvider::USER_SEARCH_CONTEXT:
                return ContextProvider::USER_SEARCH_PARENT_ROUTE;
            case ContextProvider::AE_CONTEXT:
                return ContextProvider::AE_PARENT_ROUTE;
            case ContextProvider::VTS_CONTEXT:
                return ContextProvider::VTS_PARENT_ROUTE;
            default:
                throw new \Exception();
        }
    }

    public function getTestLogsRoute()
    {
        return $this->getRoute().self::TEST_LOGS;
    }

    public function getTestQualityRoute()
    {
        return $this->getRoute().self::TEST_QUALITY;
    }
}
