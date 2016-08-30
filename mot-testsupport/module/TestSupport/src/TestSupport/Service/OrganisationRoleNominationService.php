<?php

namespace TestSupport\Service;

use Doctrine\ORM\EntityManager;
use DvsaCommon\UrlBuilder\OrganisationUrlBuilder;
use TestSupport\Helper\TestSupportAccessTokenManager;
use TestSupport\Helper\TestSupportRestClientHelper;

class OrganisationRoleNominationService
{
    /**
     * @var TestSupportRestClientHelper $testSupportRestClientHelper
     */
    private $testSupportRestClientHelper;

    /**
     * @var EntityManager $entityManager
     */
    private $entityManager;

    public function __construct(TestSupportRestClientHelper $testSupportRestClientHelper, EntityManager $entityManager)
    {
        $this->testSupportRestClientHelper = $testSupportRestClientHelper;
        $this->entityManager = $entityManager;
    }

    public function nominateUser($userId, $orgId, $roleCode)
    {
        $data = [];
        TestSupportAccessTokenManager::addSchemeManagerAsRequestorIfNecessary($data);
        $restClient = $this->testSupportRestClientHelper->getJsonClient($data);

        $positionPath = OrganisationUrlBuilder::position($orgId);

        $roleId = $this->getRoleIdFromCode($roleCode);

        $return = $restClient->post($positionPath, [
            'nomineeId' => $userId,
            'roleId'  => $roleId,
        ]);

        if (! isset($return['data'])) {
                throw new \Exception('Failed to add permission to organisation id '.$orgId);
        }

        return $return['data'];
    }

    private function getRoleIdFromCode($roleCode)
    {
        $result = $this->entityManager->getConnection()->executeQuery(
            "SELECT id FROM organisation_business_role WHERE code = :role_code",
            ['role_code' =>$roleCode]
        )->fetch();

        return $result['id'];
    }
}
