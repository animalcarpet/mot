<?php

namespace TestSupport\Helper;

use DvsaCommon\Enum\OrganisationBusinessRoleId;
use DvsaCommon\HttpRestJson\Client;
use DvsaCommon\UrlBuilder\OrganisationUrlBuilder;

/**
 * Nominate a user for a role in an AE.
 */
trait NominatorTrait
{
    /**
     * To Nominate a user for a role in an AE.
     *
     * @param Client $client
     * @param $nomineeId
     * @param $organisationRoleId
     * @param $organisationIds
     *
     * @throws \Exception
     */
    private function nominateUserForRoleInAes(Client $client, $nomineeId, $organisationRoleId, $organisationIds)
    {
        if (!in_array($organisationRoleId, OrganisationBusinessRoleId::getAll())) {
            throw new \Exception('Provided role ID is not available. see DvsaCommon\Enum\OrganisationBusinessRoleId');
        }

        foreach ($organisationIds as $aeId) {
            $response = $client->post(
                OrganisationUrlBuilder::position($aeId)->toString(),
                [
                    'nomineeId' => $nomineeId,
                    'roleId' => $organisationRoleId,
                ]
            );
        }
    }

    private function activateBusinessRoleForPersonInOrganisation($personId)
    {
        $em = $this->getServiceLocator()->get(\Doctrine\ORM\EntityManager::class);
        $stmt = $em->getConnection()->prepare(
            "UPDATE organisation_business_role_map SET status_id =
                                                           (SELECT `id` FROM `business_role_status` WHERE `code` = '" .\DvsaCommon\Enum\BusinessRoleStatusCode::ACTIVE."')
                                               WHERE person_id = ?"
        );
        $stmt->bindValue(1, $personId);
        $stmt->execute();

        return true;
    }
}
