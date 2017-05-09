<?php

namespace TestSupport\Controller;

use TestSupport\Helper\NominatorTrait;
use TestSupport\Helper\RestClientGetterTrait;
use TestSupport\Service\AedmService;
use Zend\View\Model\JsonModel;

/**
 * Creates AEDMs for use by tests.
 *
 * Should not be deployed in production.
 */
class AedmDataController extends BaseTestSupportRestfulController
{
    use RestClientGetterTrait;
    use NominatorTrait;

    protected $accountPerson;

    /**
     * @param mixed $data optional data with differentiator,
     *                    requestor => {username,password} DVSA scheme management user with whom to assign AEDM role
     *                    aeIds => IDs of AEs for which the user is an AEDM
     *
     * @return void|JsonModel username of new AEDM
     */
    public function create($data)
    {
        return $this->getAedmService()->create($data);
    }

    /**
     * @return AedmService
     */
    private function getAedmService()
    {
        return $this->getServiceLocator()->get(AedmService::class);
    }
}
