<?php
namespace OrganisationApi\Controller;

use DvsaCommonApi\Controller\AbstractDvsaRestfulController;
use DvsaCommonApi\Model\ApiResponse;
use DvsaCommonApi\Transaction\TransactionAwareInterface;
use DvsaCommonApi\Transaction\TransactionAwareTrait;
use OrganisationApi\Service\NominateRoleService;
use OrganisationApi\Service\NominateRoleServiceBuilder;
use OrganisationApi\Service\OrganisationPositionService;
use OrganisationApi\Service\Validator\NominateRoleValidator;

/**
 * Class OrganisationPositionController
 *
 * @package OrganisationApi\Controller
 */
class OrganisationPositionController extends AbstractDvsaRestfulController implements TransactionAwareInterface
{
    use TransactionAwareTrait;

    public function __construct()
    {
        $this->setIdentifierName("organisationId");
    }

    public function get($organisationId)
    {
        $organisationId = $this->params()->fromRoute('organisationId');
        $service        = $this->getOrganisationPositionService();
        $positions      = $service->getListForOrganisation($organisationId);

        return ApiResponse::jsonOk($positions);
    }

    public function create($data)
    {
        $organisationId = $this->params()->fromRoute('organisationId');

        $validator = new NominateRoleValidator();
        $validator->validate($data);

        $nomineeId = $data['nomineeId'];
        $roleId    = $data['roleId'];

        $service = $this->getNominateRoleServiceBuilder()
            ->buildForNominationCreation($nomineeId, $organisationId, $roleId);
        $organisationPosition = $service->nominateRole();

        return ApiResponse::jsonOk(['id' => $organisationPosition->getId()]);
    }

    public function update($id, $data)
    {
        $nomineeId = $data['nomineeId'];
        $roleCode = $data['roleId'];
        $organisationId = intval($this->params()->fromRoute('organisationId'));

        $nominateRoleService = $this->getNominateRoleServiceBuilder()
            ->buildForNominationUpdate($nomineeId, $organisationId, $roleCode);

        $position = $nominateRoleService->updateRoleNominationNotification();

        return ApiResponse::jsonOk(['id' => $position->getId()]);
    }

    /**
     * Removes organisation position of a person
     *
     * @param string $organisationId
     *
     * @return \Zend\View\Model\JsonModel
     */
    public function delete($organisationId)
    {
        $organisationPositionId = $this->params()->fromRoute("positionId");
        $this->getOrganisationPositionService()->remove((int) $organisationId, (int) $organisationPositionId);

        return ApiResponse::jsonOk();
    }

    /**
     *
     * @return OrganisationPositionService
     */
    private function getOrganisationPositionService()
    {
        return $this->getServiceLocator()->get(OrganisationPositionService::class);
    }

    /**
     *
     * @return NominateRoleServiceBuilder
     */
    private function getNominateRoleServiceBuilder()
    {
        return $this->getServiceLocator()->get(NominateRoleServiceBuilder::class);
    }
}
