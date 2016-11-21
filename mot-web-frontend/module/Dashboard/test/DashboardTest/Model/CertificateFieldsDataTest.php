<?php

namespace Dvsa\Mot\Frontend\PersonModuleTest\Model;

use Core\ViewModel\Badge\Badge;
use Dvsa\Mot\Frontend\PersonModule\Model\CertificateFieldsData;

/**
 * Class CertificateFieldsDataTest
 *
 * @package DashboardTest\Model
 */
class CertificateFieldsDataTest extends \PHPUnit_Framework_TestCase
{
    const CERTIFICATE_NO = 1;
    const CERTIFICATE_DATE = '12.12.2015';
    const SIDEBAR_BADGE = 'badge';

    public function test_getterSetters_shouldBeOk()
    {
        $sidebarBadge = new Badge(self::SIDEBAR_BADGE);
        $certificateFieldsData = new CertificateFieldsData(self::CERTIFICATE_NO, self::CERTIFICATE_DATE, $sidebarBadge::normal());
        $this->assertEquals(self::CERTIFICATE_NO, $certificateFieldsData->getCertificateNo());
        $this->assertEquals(self::CERTIFICATE_DATE, $certificateFieldsData->getCertificatDate());
        $this->assertEquals($sidebarBadge, $certificateFieldsData->getSidebarBadge());
    }
}
