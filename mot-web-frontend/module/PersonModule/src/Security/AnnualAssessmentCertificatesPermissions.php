<?php
namespace Dvsa\Mot\Frontend\PersonModule\Security;

use Application\Data\ApiPersonalDetails;
use Dashboard\Model\PersonalDetails;
use DvsaCommon\Auth\MotAuthorisationServiceInterface;
use DvsaCommon\Auth\PermissionInSystem;
use DvsaCommon\Exception\UnauthorisedException;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use DvsaCommon\Model\DvsaRole;

class AnnualAssessmentCertificatesPermissions implements AutoWireableInterface
{
    /** @var  ApiPersonalDetails */
    private $personalDetailsService;

    /** @var MotAuthorisationServiceInterface */
    private $authorisationService;

    public function __construct(
        ApiPersonalDetails $personalDetailsService,
        MotAuthorisationServiceInterface $authorisationService
    ) {
        $this->personalDetailsService = $personalDetailsService;
        $this->authorisationService = $authorisationService;
    }

    /**
     * @throws UnauthorisedException
     */
    public function assertGrantedView($targetPersonId, $loggedInPersonId)
    {
        $this->assertGranted(
            PermissionInSystem::VIEW_MOT_TESTING_ANNUAL_CERTIFICATE_FOR_USER,
            $targetPersonId,
            $loggedInPersonId
        );
    }

    /**
     * @param $targetPersonId
     * @param $loggedInPersonId
     * @return bool
     */
    public function isGrantedView($targetPersonId, $loggedInPersonId)
    {
        try {
            $this->assertGrantedView($targetPersonId, $loggedInPersonId);
        } catch (UnauthorisedException $exception) {
            return false;
        }

        return true;
    }

    /**
     * @param $targetPersonId
     * @param $loggedInPersonId
     * @throws UnauthorisedException
     */
    public function assertGrantedCreate($targetPersonId, $loggedInPersonId)
    {
        $this->assertGranted(
            PermissionInSystem::CREATE_MOT_TESTING_ANNUAL_CERTIFICATE_FOR_USER,
            $targetPersonId,
            $loggedInPersonId
        );
    }

    /**
     * @param $targetPersonId
     * @param $loggedInPersonId
     * @return bool
     */
    public function isGrantedCreate($targetPersonId, $loggedInPersonId)
    {
        try {
            $this->assertGrantedCreate($targetPersonId, $loggedInPersonId);
        } catch (UnauthorisedException $exception) {
            return false;
        }

        return true;
    }

    /**
     * @param $targetPersonId
     * @param $loggedInPersonId
     * @throws UnauthorisedException
     */
    public function assertGrantedUpdate($targetPersonId, $loggedInPersonId)
    {
        $this->assertGranted(
            PermissionInSystem::UPDATE_MOT_TESTING_ANNUAL_CERTIFICATE_FOR_USER,
            $targetPersonId,
            $loggedInPersonId
        );
    }

    /**
     * @param $targetPersonId
     * @param $loggedInPersonId
     * @return bool
     */
    public function isGrantedUpdate($targetPersonId, $loggedInPersonId)
    {
        try {
            $this->assertGrantedUpdate($targetPersonId, $loggedInPersonId);
        } catch (UnauthorisedException $exception) {
            return false;
        }

        return true;
    }

    /**
     * @param $targetPersonId
     * @param $loggedInPersonId
     * @throws UnauthorisedException
     */
    public function assertGrantedRemove($targetPersonId, $loggedInPersonId)
    {
        $this->assertGranted(
            PermissionInSystem::REMOVE_MOT_TESTING_ANNUAL_CERTIFICATE_FOR_USER,
            $targetPersonId,
            $loggedInPersonId
        );
    }

    /**
     * @param $targetPersonId
     * @param $loggedInPersonId
     * @return bool
     */
    public function isGrantedRemove($targetPersonId, $loggedInPersonId)
    {
        try {
            $this->assertGrantedRemove($targetPersonId, $loggedInPersonId);
        } catch (UnauthorisedException $exception) {
            return false;
        }

        return true;
    }

    /**
     * @param array $personSystemRoles
     * @return bool
     */
    private function hasDvsaRole(array $personSystemRoles)
    {
        foreach ($personSystemRoles as $role) {
            if (DvsaRole::isDvsaRole($role)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param $permission
     * @param $targetPersonId
     * @param $loggedInPersonId
     * @throws UnauthorisedException
     */
    private function assertGranted($permission, $targetPersonId, $loggedInPersonId)
    {
        if ($loggedInPersonId !== $targetPersonId) {
            $this->authorisationService->assertGranted($permission);
        } else {
            $personalDetails = new PersonalDetails(
                $this->personalDetailsService->getPersonalDetailsData($loggedInPersonId)
            );

            if ($this->hasDvsaRole($personalDetails->getRolesAndAssociations()['system']['roles'])) {
                throw new UnauthorisedException("You don't have permission");
            }
        }
    }
}