<?php

namespace DvsaMotTest\Flash;

use Core\Action\FlashNamespace;

class VehicleCertificateSearchFlashMessage
{
    // Used when searching for
    const NOT_FOUND = 'NOT-FOUND';

    public static function getNamespace()
    {
        return new FlashNamespace('vehicle-search');
    }
}
