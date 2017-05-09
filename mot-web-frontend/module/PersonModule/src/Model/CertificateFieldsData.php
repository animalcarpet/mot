<?php

namespace Dvsa\Mot\Frontend\PersonModule\Model;

use Core\ViewModel\Badge\Badge;

/**
 * Data for Certificate Fields Data.
 */
class CertificateFieldsData
{
    private $certificateNo;
    private $certificateDate;
    /** @var Badge */
    private $sidebarBadge;

    public function __construct($certificateNo, $certificateDate, $sidebarBadge)
    {
        $this->certificateNo = $certificateNo;
        $this->certificateDate = $certificateDate;
        $this->sidebarBadge = $sidebarBadge;
    }

    public function getCertificateNo()
    {
        return $this->certificateNo;
    }

    public function getCertificatDate()
    {
        return $this->certificateDate;
    }

    public function getSidebarBadge()
    {
        return $this->sidebarBadge;
    }
}
