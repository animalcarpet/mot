<?php

namespace TestSupport\Service;

use TestSupport\Helper\TestSupportAccessTokenManager;
use Zend\View\Model\JsonModel;
use DvsaCommon\Enum\OrganisationBusinessRoleCode;
use DvsaCommon\Enum\OrganisationBusinessRoleId;
use TestSupport\FieldValidation;
use TestSupport\Helper\DataGeneratorHelper;
use TestSupport\Helper\TestDataResponseHelper;
use TestSupport\Model\Account;
use TestSupport\Model\AccountPerson;
use Doctrine\ORM\EntityManager;
use DvsaCommon\HttpRestJson\Client;
use DvsaCommon\UrlBuilder\OrganisationUrlBuilder;
use DvsaCommon\Enum\BusinessRoleStatusCode;

class AedmService
{
    /** @var AccountDataService */
    private $accountDataService;

    /** @var AccountService */
    private $accountService;

    /** @var EntityManager */
    private $em;

    /** @var Client */
    private $restClient;

    public function __construct(
        AccountDataService $accountDataService,
        AccountService $accountService,
        EntityManager $em,
        Client $restClient
    ) {
        $this->accountDataService = $accountDataService;
        $this->accountService = $accountService;
        $this->em = $em;
        $this->restClient = $restClient;
    }

    /**
     * Create a AO1 with the data supplied.
     *
     * @param array $data
     *
     * @return JsonModel
     */
    public function create(array $data)
    {
        TestSupportAccessTokenManager::addSchemeManagerAsRequestorIfNecessary($data);

        FieldValidation::checkForRequiredFieldsInData(['aeIds'], $data);

        if (!isset($data['personId'])) {
            $dataGeneratorHelper = DataGeneratorHelper::buildForDifferentiator($data);

            $accountPerson = new AccountPerson($data, $dataGeneratorHelper);
            $account = $this->accountService->createAccount(
                OrganisationBusinessRoleCode::AUTHORISED_EXAMINER_DESIGNATED_MANAGER,
                $dataGeneratorHelper, $accountPerson
            );
        } else {
            $account = new Account($data);
        }

        $this->nominateUserForRoleInAes(
            $account->getPersonId(),
            OrganisationBusinessRoleId::AUTHORISED_EXAMINER_DESIGNATED_MANAGER,
            $data['aeIds']
        );

        $this->activateBusinessRoleForPersonInOrganisation($account->getPersonId());

        return TestDataResponseHelper::jsonOk(
            [
                'message' => 'Authorised Examiner (Designated Manager) created',
                'username' => $account->getUsername(),
                'password' => $account->getPassword(),
                'personId' => $account->getPersonId(),
                'firstName' => $account->getFirstName(),
                'middleName' => ($accountPerson) ? $accountPerson->getMiddleName() : '',
                'surname' => $account->getSurname(),
            ]
        );
    }

    private function nominateUserForRoleInAes($nomineeId, $organisationRoleId, $organisationIds)
    {
        if (!in_array($organisationRoleId, OrganisationBusinessRoleId::getAll())) {
            throw new \Exception('Provided role ID is not available. see DvsaCommon\Enum\OrganisationBusinessRoleId');
        }

        foreach ($organisationIds as $aeId) {
            $this->restClient->post(
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
        $stmt = $this->em->getConnection()->prepare(
            'UPDATE organisation_business_role_map SET status_id =
            (SELECT `id` FROM `business_role_status` WHERE `code` = ?)
             WHERE person_id = ?'
        );
        $stmt->bindValue(1, BusinessRoleStatusCode::ACTIVE);
        $stmt->bindValue(2, $personId);
        $stmt->execute();

        return true;
    }
}
