<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link https://gitlab.motdev.org.uk/mot/mot
 */

namespace Dvsa\Mot\Frontend\MotTestModuleTest\ViewModel;

use Dvsa\Mot\Frontend\MotTestModule\ViewModel\ObservedDefect;

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
        $observedDefect = new ObservedDefect($defectType, 'lateralLocation', 'longitudinalLocation',
            'verticalLocation', 'userComment', false, 'name', 1, $defectId, false);

        $this->assertEquals($result, $observedDefect->isManualAdvisory());

    }

    /**
     * @return array
     */
    public function manualAdvisoryProvider()
    {
        return [
            [ObservedDefect::ADVISORY, 0, true],
            [ObservedDefect::FAILURE, 0, false],
            [ObservedDefect::ADVISORY, 1234, false],
        ];
    }
}
