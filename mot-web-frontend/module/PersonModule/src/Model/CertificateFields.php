<?php

namespace Dvsa\Mot\Frontend\PersonModule\Model;

use Core\ViewModel\Badge\Badge;
use DvsaCommon\Enum\AuthorisationForTestingMotStatusCode;

/**
 * Data for Certificate Fields.
 */
class CertificateFields
{
    const CERTIFICATE_DATE_NOT_RECORDED = 'Not recorded';
    const CERTIFICATE_DATE_QUALIFIED = 'Qualified pre April 2016';
    const CERTIFICATE_NO_NOT_RECORDED = 'Not recorded';
    const CERTIFICATE_NO_NOT_NEEDED = 'Not needed';

    public function __construct()
    {
    }

    public function getCertificateFields($qualificationStatus)
    {
        switch ($qualificationStatus) {
            case AuthorisationForTestingMotStatusCode::INITIAL_TRAINING_NEEDED:
                return new CertificateFieldsData(self::CERTIFICATE_NO_NOT_RECORDED, self::CERTIFICATE_DATE_NOT_RECORDED, Badge::normal());
                break;
            case AuthorisationForTestingMotStatusCode::DEMO_TEST_NEEDED:
                return new CertificateFieldsData(self::CERTIFICATE_NO_NOT_RECORDED, self::CERTIFICATE_DATE_NOT_RECORDED, Badge::warning());
                break;
            case AuthorisationForTestingMotStatusCode::QUALIFIED:
                return new CertificateFieldsData(self::CERTIFICATE_NO_NOT_NEEDED, self::CERTIFICATE_DATE_QUALIFIED, Badge::success());
                break;
            case AuthorisationForTestingMotStatusCode::SUSPENDED:
                return new CertificateFieldsData(self::CERTIFICATE_NO_NOT_NEEDED, self::CERTIFICATE_DATE_QUALIFIED, Badge::alert());
                break;
            default:
                return new CertificateFieldsData(self::CERTIFICATE_NO_NOT_RECORDED, self::CERTIFICATE_DATE_NOT_RECORDED, Badge::normal());
        }
    }
}
