<?php

namespace OrganisationApi\Service;

use DvsaCommonApi\Service\AbstractService;
use DvsaEntities\Repository\SiteRepository;

class AuthorisedExaminerStatusService extends AbstractService
{
    private $siteRepository;

    /**
     * @param SiteRepository $siteRepository
     */
    public function __construct(
        SiteRepository $siteRepository
    ) {
        $this->siteRepository = $siteRepository;
    }

    /**
     * This answers a list of all of the area offices currently active in the system.
     * The returned structure contains a LIST of PROPERTIES, as the list of Area Office
     * numbers is not contiguous.
     *
     * @return array
     */
    public function getAllAreaOffices()
    {
        return $this->siteRepository->getAllAreaOffices();
    }
}
