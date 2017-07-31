<?php

namespace Site\Service;

use Core\Routing\AeRoutes;
use Core\Routing\VtsRoutes;
use DvsaCommon\Auth\MotAuthorisationServiceInterface;
use DvsaCommon\Auth\PermissionAtOrganisation;
use DvsaCommon\Auth\PermissionInSystem;
use DvsaCommon\Dto\Site\SiteDto;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use Zend\View\Helper\Url;

class SiteBreadcrumbsBuilder implements AutoWireableInterface
{
    private $urlHelper;
    private $auth;

    public function __construct(
        Url $urlHelper,
        MotAuthorisationServiceInterface $auth
    )
    {
        $this->urlHelper = $urlHelper;
        $this->auth = $auth;
    }

    public function buildBreadcrumbs(SiteDto $siteDto, bool $linkToSite)
    {
        return $this->getAeBreadcrumb($siteDto) + $this->getVtsBreadcrumb($siteDto, $linkToSite);
    }

    public function getAeBreadcrumb(SiteDto $site):array
    {
        $org = $site->getOrganisation();

        if (!is_null($org)) {
            if ($this->canAccessAePage($org->getId())) {
                return [$org->getName() => AeRoutes::of($this->urlHelper)->ae($org->getId())];
            }
        }

        return [];
    }

    public function getVtsBreadcrumb(SiteDto $site, $linkToSite)
    {
        $vtsLink = VtsRoutes::of($this->urlHelper)->vts($site->getId());

        return [$site->getName() => $linkToSite ? $vtsLink : null];
    }

    /**
     * @param int $orgId
     * @return bool
     */
    private function canAccessAePage($orgId)
    {
        return
            $this->auth->isGranted(PermissionInSystem::AUTHORISED_EXAMINER_READ_FULL) ||
            $this->auth->isGrantedAtOrganisation(PermissionAtOrganisation::AUTHORISED_EXAMINER_READ, $orgId);
    }
}