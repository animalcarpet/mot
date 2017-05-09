<?php

namespace TestSupport\Service;

use DvsaCommon\UrlBuilder\SiteUrlBuilder;
use TestSupport\Helper\TestSupportAccessTokenManager;
use TestSupport\Helper\TestSupportRestClientHelper;

class SiteRoleNominationService
{
    /**
     * @var TestSupportRestClientHelper
     */
    private $testSupportRestClientHelper;

    public function __construct(TestSupportRestClientHelper $testSupportRestClientHelper)
    {
        $this->testSupportRestClientHelper = $testSupportRestClientHelper;
    }

    public function nominateUserForRoleAtSite($userId, $siteId, $roleCode)
    {
        $data = [];
        TestSupportAccessTokenManager::addSchemeManagerAsRequestorIfNecessary($data);
        $restClient = $this->testSupportRestClientHelper->getJsonClient($data);

        $positionPath = SiteUrlBuilder::site($siteId)->position()->toString();

        $return = $restClient->post($positionPath, [
            'nomineeId' => $userId,
            'roleCode' => $roleCode,
        ]);

        if (!isset($return['data'])) {
            throw new \Exception('Failed to add permission to siteId '.$siteId);
        }

        return $return['data'];
    }
}
