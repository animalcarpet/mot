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

    public function buildBreadcrumbs(SiteDto $siteDto)
    {
        $breadcrumbs = [
            $siteDto->getName() => VtsRoutes::of($this->urlHelper)->vts($siteDto->getId()),
        ];
        $breadcrumbs = $this->prependBreadcrumbsWithAeLink($siteDto, $breadcrumbs);

        return $breadcrumbs;
    }

    /**
     * @param SiteDto $site
     * @param array   $breadcrumbs
     *
     * @return array
     */
    private function prependBreadcrumbsWithAeLink(SiteDto $site, &$breadcrumbs)
    {
        $org = $site->getOrganisation();

        if ($org) {
            $canVisitAePage = $this->canAccessAePage($org->getId());

            if ($canVisitAePage) {
                $aeBreadcrumb = [$org->getName() => AeRoutes::of($this->urlHelper)->ae($org->getId())];
                $breadcrumbs = $aeBreadcrumb + $breadcrumbs;
            }
        }

        return $breadcrumbs;
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