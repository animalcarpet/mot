<?php

namespace DvsaMotApiTest\Controller\Validator;

use DvsaCommon\Dto\Site\VehicleTestingStationDto;
use DvsaMotApi\Controller\Validator\InspectionLocationValidator;

/**
 * Class InspectionLocationValidatorTest.
 */
class InspectionLocationValidatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \DvsaCommonApi\Service\Exception\BadRequestException
     */
    public function testCreatesWithExpectedDefaultKeysAndRejectsExcessiveData()
    {
        $v = new InspectionLocationValidator();
        $v->validate(
            [
                'siteid' => 'V1234',
                'location' => 'this is excess informaiton',
            ]
        );
    }

    /**
     * @expectedException \DvsaCommonApi\Service\Exception\BadRequestException
     */
    public function testCreatesWithCustomKeysAndRejectsWithExcessiveData()
    {
        $v = new InspectionLocationValidator('eric', 'ernie');
        $v->validate(
            [
                'eric' => 'V1234',
                'ernie' => 'this is excess informaiton',
            ]
        );
    }

    public function testCreatesWithDefaultsAndAcceptsJustAsiteIdButDoesNotYetValidateIt()
    {
        $v = new InspectionLocationValidator();
        $v->validate(['siteid' => 'V1234']);
        $this->assertEquals($v->getSiteIdKey(), 'siteid');
        $this->assertEquals($v->getLocationKey(), 'location');
    }

    public function testCreatesWithDefaultsAndAcceptsJustAlocation()
    {
        $v = new InspectionLocationValidator();
        $v->validate(['location' => 'in my unit testing code lol etc']);
        $this->assertEquals($v->getLocation(), 'in my unit testing code lol etc');
        $this->assertNull($v->getSiteId());
        $this->assertEquals('siteid', $v->getSiteIdKey());
    }

    /**
     * @expectedException \DvsaCommonApi\Service\Exception\BadRequestException
     */
    public function testRejectsInvalidSiteNumberWhenServiceAvailable()
    {
        $v = new InspectionLocationValidator();
        $v->validate(
            ['siteid' => '42, site-id-fail'],
            $this // SiteService
        );
    }

    /**
     * @expectedException \DvsaCommonApi\Service\Exception\BadRequestException
     */
    public function testRejectsInvalidSiteNumberWithSpecCharsWhenServiceAvailable()
    {
        $v = new InspectionLocationValidator();
        $v->validate(
            ['siteid' => '!@£$%^&'],
            $this // SiteService
        );
    }

    public function testAcceptsAndVerifiesValidSiteId()
    {
        $v = new InspectionLocationValidator();
        $v->validate(
            ['siteid' => 'V1234'],
            $this // SiteService
        );
        $this->assertEquals($v->getSiteId(), 42);
        $this->assertEquals($v->getSiteName(), 'V1234');
    }

    /** MOCK: returning a valid id for a site string */
    public function getSiteBySiteNumber($id)
    {
        if ('V1234' == $id) {
            return (new VehicleTestingStationDto())
                ->setName('V1234')
                ->setId(42);
        }

        return null;
    }
}
