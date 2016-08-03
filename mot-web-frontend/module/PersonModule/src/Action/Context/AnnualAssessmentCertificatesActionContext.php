<?php

namespace Dvsa\Mot\Frontend\PersonModule\Action\Context;

use Dvsa\Mot\Frontend\PersonModule\Model\FormContext;

class AnnualAssessmentCertificatesActionContext extends FormContext
{
    private $certificateId;

    public function __construct($targetPersonId, $loggedInPersonId, $group, $controller, $certificateId)
    {
        parent::__construct($targetPersonId, $loggedInPersonId, $group, $controller);

        $this->certificateId = $certificateId;
    }

    public function getCertificateId()
    {
        return $this->certificateId;
    }

    public function setCertificateId($certificateId)
    {
        $this->certificateId = $certificateId;
    }
}
