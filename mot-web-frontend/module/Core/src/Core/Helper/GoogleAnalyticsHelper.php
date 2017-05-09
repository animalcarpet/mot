<?php

namespace Core\Helper;

use Zend\View\Helper\AbstractHelper;

class GoogleAnalyticsHelper extends AbstractHelper
{
    private $gaTrackingCode;

    public function __construct($gaTrackingCode)
    {
        $this->gaTrackingCode = $gaTrackingCode;
    }

    public function getGATrackingCode()
    {
        return $this->gaTrackingCode;
    }
}
