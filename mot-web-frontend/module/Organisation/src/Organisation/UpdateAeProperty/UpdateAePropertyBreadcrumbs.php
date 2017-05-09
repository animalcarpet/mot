<?php

namespace Organisation\UpdateAeProperty;

use DvsaCommon\Auth\MotAuthorisationServiceInterface;
use DvsaCommon\Dto\Organisation\OrganisationDto;
use Zend\View\Helper\Url;

class UpdateAePropertyBreadcrumbs extends AeBreadcrumbs
{
    private $label;

    public function __construct(
        OrganisationDto $ae,
        MotAuthorisationServiceInterface $authorisationService,
        Url $url,
        $label
    ) {
        parent::__construct($ae, $authorisationService, $url);
        $this->label = $label;
    }

    public function create()
    {
        $aeBreadcrumbs = parent::create();

        return array_merge($aeBreadcrumbs, [$this->label => '']);
    }
}
