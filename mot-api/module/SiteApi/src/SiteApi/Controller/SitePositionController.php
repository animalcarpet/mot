<?php

namespace SiteApi\Controller;

use DvsaCommonApi\Controller\AbstractDvsaRestfulController;
use DvsaCommonApi\Model\ApiResponse;
use DvsaCommonApi\Transaction\TransactionAwareInterface;
use DvsaCommonApi\Transaction\TransactionAwareTrait;
use SiteApi\Service\NominateRoleService;
use SiteApi\Service\SitePositionService;
use SiteApi\Service\Validator\NominateRoleValidator;

/**
 * Class SitePositionController.
 */
class SitePositionController extends AbstractDvsaRestfulController implements TransactionAwareInterface
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

        $position = $this->getNominateRoleService()->nominateRole($siteId, $nomineeId, $roleCode);

        return ApiResponse::jsonOk(['id' => $position->getId()]);
    }

    public function update($id, $data)
    {
        $nomineeId = $data['nomineeId'];
        $roleCode = $data['roleCode'];
        $siteId = intval($this->params()->fromRoute('siteId'));

        $position = $this->getNominateRoleService()->updateRoleNominationNotification($siteId, $nomineeId, $roleCode);

        return ApiResponse::jsonOk(['id' => $position->getId()]);
    }

    /**
     * Removes site position of a person.
     *
     * @param string $siteId
     *
     * @return \Zend\View\Model\JsonModel
     */
    public function delete($siteId)
    {
        $this->inTransaction(
            function () use ($siteId) {
                $sitePositionId = $this->params()->fromRoute('positionId');
                $this->getSitePositionService()->remove((int) $siteId, (int) $sitePositionId);
            }
        );

        return ApiResponse::jsonOk();
    }

    /**
     * @return SitePositionService
     */
    private function getSitePositionService()
    {
        return $this->getServiceLocator()->get(SitePositionService::class);
    }

    /**
     * @return NominateRoleService
     */
    private function getNominateRoleService()
    {
        return $this->getServiceLocator()->get(NominateRoleService::class);
    }
}
