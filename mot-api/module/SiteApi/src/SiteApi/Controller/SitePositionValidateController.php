<?php

namespace SiteApi\Controller;

use DvsaCommonApi\Controller\AbstractDvsaRestfulController;
use DvsaCommonApi\Model\ApiResponse;
use DvsaCommonApi\Transaction\TransactionAwareInterface;
use DvsaCommonApi\Transaction\TransactionAwareTrait;
use SiteApi\Service\NominateRoleService;
use SiteApi\Service\Validator\NominateRoleValidator;

/**
 * Class SitePositionValidateController.
 */
class SitePositionValidateController extends AbstractDvsaRestfulController implements TransactionAwareInterface
{
    use TransactionAwareTrait;

    public function __construct()
    {
        $this->setIdentifierName('siteId');
    }

    public function create($data)
    {
        $siteId = $this->params()->fromRoute('siteId');

        $validator = new NominateRoleValidator();
        $validator->validate($data);

        $nomineeId = $data['nomineeId'];
        $roleCode = $data['roleCode'];

        $this->getNominateRoleService()->verifyNomination($siteId, $nomineeId, $roleCode);

        return ApiResponse::jsonOk(['true']);
    }

    /**
     * @return NominateRoleService
     */
    private function getNominateRoleService()
    {
        return $this->getServiceLocator()->get(NominateRoleService::class);
    }
}
