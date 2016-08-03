<?php

namespace DvsaCommon\Auth\Assertion;

use DvsaCommon\Auth\MotAuthorisationServiceInterface;
use DvsaCommon\Auth\PermissionAtSite;
use DvsaCommon\Exception\UnauthorisedException;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;

/**
 * Assertion(s) for viewing TQI on VTS
 */
class ViewVtsTestQualityAssertion implements AutoWireableInterface
{
    /**
     * @var MotAuthorisationServiceInterface
     */
    private $authorisationService;

    /**
     * @param MotAuthorisationServiceInterface $authorisationService
     */
    public function __construct(MotAuthorisationServiceInterface $authorisationService)
    {
        $this->authorisationService = $authorisationService;
    }

    /**
     * @param $siteId int
     * @throws UnauthorisedException
     */
    public function assertGranted($siteId)
    {
        $this->authorisationService->assertGrantedAtSite(PermissionAtSite::VTS_VIEW_TEST_QUALITY, $siteId);
    }

    public function isGranted($siteId)
    {
        try {
            $this->assertGranted($siteId);
        } catch (UnauthorisedException $exception) {
            return false;
        }

        return true;
    }
}
