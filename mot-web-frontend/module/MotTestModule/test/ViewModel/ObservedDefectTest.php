<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link https://gitlab.motdev.org.uk/mot/mot
 */

namespace Dvsa\Mot\Frontend\MotTestModuleTest\ViewModel;

use Dvsa\Mot\Frontend\MotTestModule\ViewModel\IdentifiedDefect;
use DvsaCommon\Enum\RfrDeficiencyCategoryCode;

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
            'verticalLocation', 'userComment', false, 'name', 1, $defectId, false, false, false,
            RfrDeficiencyCategoryCode::PRE_EU_DIRECTIVE, true);

        $temp = '';

        $this->assertEquals($result, $identifiedDefect->isManualAdvisory());
    }

    public function testGetLocationStringWithLocation()
    {
        $identifiedDefect = new IdentifiedDefect(IdentifiedDefect::ADVISORY, 'lateralLocation', 'longitudinalLocation',
            'verticalLocation', 'userComment', false, 'name', 1, 1, false, false, false,
            RfrDeficiencyCategoryCode::PRE_EU_DIRECTIVE, true);

        $this->assertEquals(
            'LateralLocation, longitudinalLocation, verticalLocation',
            $identifiedDefect->getLocationString()
        );
    }

    public function testGetLocationStringWithoutLocation()
    {
        $identifiedDefect = new IdentifiedDefect(IdentifiedDefect::ADVISORY, '', '',
            '', 'userComment', false, 'name', 1, 1, false, false, false,
            RfrDeficiencyCategoryCode::PRE_EU_DIRECTIVE, true);

        $this->assertEquals(
            'n/a',
            $identifiedDefect->getLocationString()
        );
    }

    public function testSetOnOriginalTest()
    {
        $identifiedDefect = new IdentifiedDefect(IdentifiedDefect::ADVISORY, 'lateralLocation', 'longitudinalLocation',
            'verticalLocation', 'userComment', false, 'name', 1, 1, false, false, false,
            RfrDeficiencyCategoryCode::PRE_EU_DIRECTIVE, true);
        $identifiedDefect->setOnOriginalTest(true);

        $this->assertEquals(true, $identifiedDefect->isOnOriginalTest());
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
