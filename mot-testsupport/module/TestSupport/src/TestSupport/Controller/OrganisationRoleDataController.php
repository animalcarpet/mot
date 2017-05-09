<?php

namespace TestSupport\Controller;

use TestSupport\FieldValidation;
use TestSupport\Helper\DataGeneratorHelper;
use TestSupport\Helper\NominatorTrait;
use TestSupport\Helper\RestClientGetterTrait;
use TestSupport\Helper\TestDataResponseHelper;
use TestSupport\Model\Account;
use TestSupport\Model\AccountPerson;
use TestSupport\Service\AccountService;
use Zend\View\Model\JsonModel;

/**
 * Creates AEDs for use by tests.
 *
 * Should not be deployed in production.
 */
class OrganisationRoleDataController extends BaseTestSupportRestfulController
{
    use RestClientGetterTrait;
    use NominatorTrait;

    /**
     * @param mixed $data optional data with differentiator,
     *                    schmUsername => username of DVSA scheme management user with whom to assign AED role
     *                    aeIds => IDs of AEs for which the user is an AED
     *                    roleId => ID of AED or AEDM that they wish to give to the user
     *
     * @return void|JsonModel username of new AED
     */
    public function create($data)
    {
        FieldValidation::checkForRequiredFieldsInData(['aeIds', 'roleId'], $data);
        $roleId = $data['roleId'];

        /** @var $accountService AccountService */
        $accountService = $this->getServiceLocator()->get(AccountService::class);
        $restClient = $this->getRestClientService($data);

        if (!isset($data['personId'])) {
            $dataGeneratorHelper = DataGeneratorHelper::buildForDifferentiator($data);
            $account = $accountService->createAccount(
                $roleId,
                $dataGeneratorHelper,
                new AccountPerson($data, $dataGeneratorHelper)
            );
        } else {
            $account = new Account($data);
        }

        $this->nominateUserForRoleInAes(
            $this->getRestClientService($data),
            $account->getPersonId(),
            $roleId,
            $data['aeIds']
        );

        $this->activateBusinessRoleForPersonInOrganisation($account->getPersonId());

        return TestDataResponseHelper::jsonOk(
            [
                'message' => 'Role for role ID: '.$roleId.' created',
                'username' => $account->getUsername(),
                'password' => $account->getPassword(),
                'personId' => $account->getPersonId(),
                'firstName' => $account->getFirstName(),
                'surname' => $account->getSurname(),
            ]
        );
    }
}
