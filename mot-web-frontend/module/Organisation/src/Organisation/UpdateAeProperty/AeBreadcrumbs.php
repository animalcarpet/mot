<?php

namespace Organisation\UpdateAeProperty;

use Core\Routing\AeRoutes;
use DvsaCommon\Auth\MotAuthorisationServiceInterface;
use DvsaCommon\Auth\PermissionAtOrganisation;
use DvsaCommon\Dto\Organisation\OrganisationDto;
use Zend\View\Helper\Url;

class AeBreadcrumbs
{
    /**
     * @var OrganisationDto
     */
    private $ae;
    private $authorisationService;
    private $url;

    /**
     * @param OrganisationDto                  $ae
     * @param MotAuthorisationServiceInterface $authorisationService
     * @param Url                              $url
     */
    public function __construct(OrganisationDto $ae, MotAuthorisationServiceInterface $authorisationService, Url $url)
    {
        $this->ae = $ae;
        $this->authorisationService = $authorisationService;
        $this->url = $url;
    }

    /**
     * @return array
     */
    public function create()
    {
        $breadcrumbs = [];
        $organisationId = $this->ae->getId();

        if ($organisationId !== null &&
            $this->authorisationService->isGrantedAtOrganisation(PermissionAtOrganisation::AUTHORISED_EXAMINER_READ, $organisationId)
        ) {
            $breadcrumbs[$this->ae->getName()] = AeRoutes::of($this->url)->ae($organisationId);
        }

        return $breadcrumbs;
    }
}
