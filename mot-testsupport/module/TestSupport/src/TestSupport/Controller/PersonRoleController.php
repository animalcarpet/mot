<?php

namespace TestSupport\Controller;

use TestSupport\Helper\TestDataResponseHelper;
use TestSupport\Service\AccountDataService;

/**
 * Manipulates roles assigned to Person.
 */
class PersonRoleController extends BaseTestSupportRestfulController
{
    /**
     * @param mixed $personId
     * @param mixed $data
     *
     * @return mixed|void
     */
    public function update($personId, $data)
    {
        /** @var $accountHelper AccountDataService */
        $accountHelper = $this->getServiceLocator()->get(AccountDataService::class);
        $role = $this->params()->fromRoute('role', null);

        if (!empty($personId) && !empty($role)) {
            $accountHelper->addRole($personId, $role);

            return TestDataResponseHelper::jsonOk(['success' => true]);
        }

        return TestDataResponseHelper::jsonError('Missing parameters');
    }

    /**
     * @param mixed $personId
     *
     * @return mixed|void
     */
    public function delete($personId)
    {
        /** @var $accountHelper AccountDataService */
        $accountHelper = $this->getServiceLocator()->get(AccountDataService::class);
        $role = $this->params()->fromRoute('role', null);

        if (!empty($personId) && !empty($role)) {
            $accountHelper->removeRole($personId, $role);

            return TestDataResponseHelper::jsonOk(['success' => true]);
        }

        return TestDataResponseHelper::jsonError('Missing parameters');
    }
}
