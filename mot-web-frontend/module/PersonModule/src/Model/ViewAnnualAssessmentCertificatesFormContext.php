<?php

namespace Dvsa\Mot\Frontend\PersonModule\Model;

class ViewAnnualAssessmentCertificatesFormContext extends FormContext
{
    private $siteId;

    public function __construct($targetPersonId, $loggedInPersonId, $controller, $siteId)
    {
        parent::__construct($targetPersonId, $loggedInPersonId, null, $controller);
        $this->siteId = $siteId;
    }

    /**
     * @return int
     */
    public function getSiteId()
    {
        return $this->siteId;
    }
}
