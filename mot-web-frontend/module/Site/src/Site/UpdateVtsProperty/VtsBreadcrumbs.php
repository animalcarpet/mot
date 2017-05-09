<?php

namespace Site\UpdateVtsProperty;

use Core\Routing\AeRoutes;
use Core\Routing\VtsRoutes;
use DvsaCommon\Auth\MotAuthorisationServiceInterface;
use DvsaCommon\Auth\PermissionAtOrganisation;
use DvsaCommon\Dto\Site\VehicleTestingStationDto;
use Zend\View\Helper\Url;

class VtsBreadcrumbs
{
    private $vts;
    private $authorisationService;
    private $url;

    /**
     * @param VehicleTestingStationDto         $vts
     * @param MotAuthorisationServiceInterface $authorisationService
     * @param Url                              $url
     */
    public function __construct(VehicleTestingStationDto $vts, MotAuthorisationServiceInterface $authorisationService, Url $url)
    {
        $this->vts = $vts;
        $this->authorisationService = $authorisationService;
        $this->url = $url;
    }

    /**
     * @return array
     */
    public function create()
    {
        $breadcrumbs = [];
        $organisationId = ($this->vts->getOrganisation()) ? $this->vts->getOrganisation()->getId() : null;

        if ($organisationId !== null &&
            $this->authorisationService->isGrantedAtOrganisation(PermissionAtOrganisation::AUTHORISED_EXAMINER_READ, $organisationId)
        ) {
            $breadcrumbs[$this->vts->getOrganisation()->getName()] = AeRoutes::of($this->url)->ae($organisationId);
        }

        $breadcrumbs[$this->vts->getName()] = VtsRoutes::of($this->url)->vts($this->vts->getId());

        return $breadcrumbs;
    }
}
