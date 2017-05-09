<?php

namespace Site\UpdateVtsProperty;

use DvsaCommon\Auth\MotAuthorisationServiceInterface;
use DvsaCommon\Dto\Site\VehicleTestingStationDto;
use Zend\View\Helper\Url;

class UpdateVtsPropertyBreadcrumbs extends VtsBreadcrumbs
{
    private $label;

    public function __construct(
        VehicleTestingStationDto $vts,
        MotAuthorisationServiceInterface $authorisationService,
        Url $url,
        $label
    ) {
        parent::__construct($vts, $authorisationService, $url);
        $this->label = $label;
    }

    public function create()
    {
        $vtsBreadcrumbs = parent::create();

        return array_merge($vtsBreadcrumbs, [$this->label => '']);
    }
}
