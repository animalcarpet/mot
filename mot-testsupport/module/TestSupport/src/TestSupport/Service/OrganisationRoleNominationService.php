<?php

namespace TestSupport\Service;

use DvsaCommon\Enum\OrganisationBusinessRoleId;
use DvsaCommon\UrlBuilder\OrganisationUrlBuilder;
use TestSupport\Helper\TestSupportAccessTokenManager;
use TestSupport\Helper\TestSupportRestClientHelper;

class OrganisationRoleNominationService
{
    /**
     * @var TestSupportRestClientHelper $testSupportRestClientHelper
     */
    private $testSupportRestClientHelper;

    public function __construct(TestSupportRestClientHelper $testSupportRestClientHelper)
    {
        $this->testSupportRestClientHelper = $testSupportRestClientHelper;
    }

    public function nominateUser($userId, $orgId, $roleId)
    {
        $data = [];
        TestSupportAccessTokenManager::addSchemeManagerAsRequestorIfNecessary($data);
        $restClient = $this->testSupportRestClientHelper->getJsonClient($data);

        $positionPath = OrganisationUrlBuilder::position($orgId);

        $return = $restClient->post($positionPath, [
            'nomineeId' => $userId,
            'roleId'  => $roleId,
        ]);

        if (! isset($return['data'])) {
                throw new \Exception('Failed to add permission to organisation id '.$orgId);
        }

        return $return['data'];
    }
}