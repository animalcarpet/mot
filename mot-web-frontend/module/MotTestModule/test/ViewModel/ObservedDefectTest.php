<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link https://gitlab.motdev.org.uk/mot/mot
 */

namespace Dvsa\Mot\Frontend\MotTestModuleTest\ViewModel;

use Dvsa\Mot\Frontend\MotTestModule\ViewModel\IdentifiedDefect;

class ObservedDefectTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider manualAdvisoryProvider
     *
     * @param string $defectType
     * @param int    $defectId
     * @param bool   $result
     */
    public function testIsManualAdvisory($defectType, $defectId, $result)
    {
        $identifiedDefect = new IdentifiedDefect($defectType, 'lateralLocation', 'longitudinalLocation',
            'verticalLocation', 'userComment', false, 'name', 1, $defectId, false, false, false);

        $temp = '';

        $this->assertEquals($result, $identifiedDefect->isManualAdvisory());
    }

    public function testGetLocationStringWithLocation()
    {
        $identifiedDefect = new IdentifiedDefect(IdentifiedDefect::ADVISORY, 'lateralLocation', 'longitudinalLocation',
            'verticalLocation', 'userComment', false, 'name', 1, 1, false, false, false);

        $this->assertEquals(
            'LateralLocation, longitudinalLocation, verticalLocation',
            $identifiedDefect->getLocationString()
        );
    }

    public function testGetLocationStringWithoutLocation()
    {
        $identifiedDefect = new IdentifiedDefect(IdentifiedDefect::ADVISORY, '', '',
            '', 'userComment', false, 'name', 1, 1, false, false, false);

        $this->assertEquals(
            'n/a',
            $identifiedDefect->getLocationString()
        );
    }

    public function testSetOnOriginalTest()
    {
        $identifiedDefect = new IdentifiedDefect(IdentifiedDefect::ADVISORY, 'lateralLocation', 'longitudinalLocation',
            'verticalLocation', 'userComment', false, 'name', 1, 1, false, false, false);
        $identifiedDefect->setOnOriginalTest(true);

        $this->assertEquals(true, $identifiedDefect->isOnOriginalTest());
    }

    public function testSetBreadcrumb()
    {
        $identifiedDefect = new IdentifiedDefect(IdentifiedDefect::ADVISORY, '', '',
            '', 'userComment', false, 'name', 1, 1, false, false, false);

        $identifiedDefect->setBreadcrumb('breadcrumb');

        $this->assertEquals('breadcrumb', $identifiedDefect->getBreadcrumb());
    }

    /**
     * @return array
     */
    public function manualAdvisoryProvider()
    {
        return [
            [IdentifiedDefect::ADVISORY, 0, true],
            [IdentifiedDefect::FAILURE, 0, false],
            [IdentifiedDefect::ADVISORY, 1234, false],
        ];
    }
}
