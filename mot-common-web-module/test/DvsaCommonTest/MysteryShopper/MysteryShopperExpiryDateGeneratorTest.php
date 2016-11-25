<?php
namespace DvsaCommonTest\MysteryShopper;

use DateInterval;
use DateTime;
use DvsaCommon\MysteryShopper\MysteryShopperExpiryDateGenerator;

/**
 * MysteryShopperExpiryDateGeneratorTest.
 */
class MysteryShopperExpiryDateGeneratorTest extends \PHPUnit_Framework_TestCase
{
    /** @var MysteryShopperExpiryDateGenerator */
    private $mysteryShopperExpiryDateGenerator;

    public function setup() {
        $this->mysteryShopperExpiryDateGenerator = new MysteryShopperExpiryDateGenerator();
    }

    public function testGetPreviousMotExpiryDate()
    {
        $previousMotExpiryDate = $this->mysteryShopperExpiryDateGenerator->getPreviousMotExpiryDate();

        $this->assertInstanceOf(DateTime::class, $previousMotExpiryDate);
    }

    public function testGetCertificateExpiryDate()
    {
        $certificateExpiryDate = $this->mysteryShopperExpiryDateGenerator->getCertificateExpiryDate();

        $this->assertInstanceOf(DateTime::class, $certificateExpiryDate);
    }

    public function testDifferenceBetweenExpiryDatesAsExpected()
    {
        $previousMotExpiryDate = $this->mysteryShopperExpiryDateGenerator->getPreviousMotExpiryDate();
        $previousMotExpiryDatePlusOneYear = $previousMotExpiryDate->add(new DateInterval('P1Y'));
        $certificateExpiryDate = $this->mysteryShopperExpiryDateGenerator->getCertificateExpiryDate();

        $this->assertEquals($previousMotExpiryDatePlusOneYear, $certificateExpiryDate);
    }
}