<?php

namespace DvsaMotTest\View\VehicleSearchResult;

use Core\Routing\MotTestRoutes;
use DvsaCommon\Auth\MotAuthorisationServiceInterface;
use DvsaCommon\Constants\DuplicateCertificateSearchType;
use Zend\Mvc\Controller\Plugin\Url;

class CertificateUrlTemplate implements VehicleSearchResultUrlTemplateInterface
{
    private $urlHelper;
    private $authorisationService;
    private $noRegistration;

    /**
     * @param int $noRegistration
     * @param MotAuthorisationServiceInterface $authorisationService
     * @param Url $urlPlugin
     */
    public function __construct($noRegistration, MotAuthorisationServiceInterface $authorisationService, Url $urlPlugin)
    {
        $this->noRegistration = $noRegistration;
        $this->authorisationService = $authorisationService;
        $this->urlHelper = $urlPlugin;
    }

    public function getUrl(array $vehicle)
    {
        if ($this->noRegistration == 0) {
            $searchParams = [
                DuplicateCertificateSearchType::SEARCH_TYPE_VIN => $vehicle['vin'],
            ];
        } else {
            $searchParams = [
                DuplicateCertificateSearchType::SEARCH_TYPE_VRM => $vehicle['registration'],
            ];
        }

        return MotTestRoutes::of($this->urlHelper)->vehicleSearchResults($searchParams);
    }
}

