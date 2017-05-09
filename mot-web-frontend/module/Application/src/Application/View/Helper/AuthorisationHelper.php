<?php

namespace Application\View\Helper;

use Zend\View\Helper\AbstractHelper;
use Core\Service\MotFrontendAuthorisationServiceInterface;

/**
 * AuthorisationHelper - helper for view.
 *
 * accessible by this->authorisationHelper() in any *.phtml file
 *
 * Could use __invoke() instead of verbose explicit delegation here.
 */
class AuthorisationHelper extends AbstractHelper implements MotFrontendAuthorisationServiceInterface
{
    /**
     * @var MotFrontendAuthorisationServiceInterface
     */
    protected $authorisationService;

    public function __construct(MotFrontendAuthorisationServiceInterface $authorisationService)
    {
        $this->authorisationService = $authorisationService;
    }

    public function isGranted($permissionName)
    {
        return $this->authorisationService->isGranted($permissionName);
    }

    public function isGrantedAtSite($permissionName, $siteId)
    {
        return $this->authorisationService->isGrantedAtSite($permissionName, $siteId);
    }

    public function isGrantedAtAnySite($permissionName)
    {
        return $this->authorisationService->isGrantedAtAnySite($permissionName);
    }

    public function isGrantedAtOrganisation($permissionName, $orgId)
    {
        return $this->authorisationService->isGrantedAtOrganisation($permissionName, $orgId);
    }

    public function assertGranted($permissionName)
    {
        $this->authorisationService->assertGranted($permissionName);
    }

    public function assertGrantedAtSite($permissionName, $siteId)
    {
        $this->authorisationService->assertGrantedAtSite($permissionName, $siteId);
    }

    public function assertGrantedAtAnySite($permissionName)
    {
        $this->authorisationService->assertGrantedAtAnySite($permissionName);
    }

    public function assertGrantedAtOrganisation($permissionName, $orgId)
    {
        $this->authorisationService->assertGrantedAtOrganisation($permissionName, $orgId);
    }

    /** @deprecated checks permissions, not roles */
    public function hasRole($roleName)
    {
        return $this->authorisationService->hasRole($roleName);
    }

    /** @deprecated check permissions, not roles */
    public function isVehicleExaminer()
    {
        return $this->authorisationService->isVehicleExaminer();
    }

    /** @deprecated check permissions, not roles */
    public function isTester()
    {
        return $this->authorisationService->isTester();
    }

    /** @deprecated check permissions, not roles */
    public function isDvsa()
    {
        return $this->authorisationService->isDvsa();
    }

    /**
     * Returns the user's roles (at any level) as an array.
     *
     * @return array
     *
     * @deprecated check permissions, rather than roles
     */
    public function getRolesAsArray()
    {
        return $this->authorisationService->getRolesAsArray();
    }
}
