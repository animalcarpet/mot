<?php

namespace Dvsa\Mot\Frontend\RegistrationModuleTest\Step;

use Dvsa\Mot\Frontend\RegistrationModule\Service\RegistrationSessionService;
use Dvsa\Mot\Frontend\RegistrationModule\Step\AbstractRegistrationStep;
use DvsaCommonTest\TestUtils\XMock;
use Zend\InputFilter\InputFilter;

/**
 * Class AbstractRegistrationStepTest.
 *
 * @group VM-11506
 */
class AbstractRegistrationStepTest extends \PHPUnit_Framework_TestCase
{
    const STEP_FIELD = "test";

    /**
     * Make sure that save to session is not called when the validator fails.
     *
     * @dataProvider dpSave
     *
     * @param bool $expected
     *
     * @throws \Exception
     */
    public function testSave($expected)
    {
        $filter = XMock::of(InputFilter::class);
        $filter->expects($this->once())
            ->method('isValid')
            ->willReturn($expected);

        $step = $this->getMockBuilder(AbstractRegistrationStep::class)
            ->setConstructorArgs([
                XMock::of(RegistrationSessionService::class),
                $filter,
            ])
            ->setMethods(['saveToSession'])
            ->getMockForAbstractClass();

        // If Valid is true then we assure saveToSession gets called
        if (true === $expected) {
            $step->expects($this->once())
                ->method('saveToSession');
        } else {
            $step->expects($this->never())
                ->method('saveToSession');
        }

        $actual = $step->save();

        $this->assertSame($expected, $actual);
    }

    public function dpSave()
    {
        return [
            [true],
            [false],
        ];
    }

    /**
     * test the validation pattern.
     *
     * @covers AccountSummaryStep::validate
     * @covers AddressStep::validate
     * @covers CompleteStep::validate
     * @covers CreateAccountStep::validate
     * @covers DetailsStep::validate
     * @covers PasswordStep::validate
     * @covers SecurityQuestionsStep::validate
     */
    public function testValidate()
    {
        $filter = XMock::of(InputFilter::class);
        $step   = $this->getMockBuilder(AbstractRegistrationStep::class)
            ->setConstructorArgs([
                XMock::of(RegistrationSessionService::class),
                $filter,
            ])
            ->setMethods(['toArray'])
            ->getMockForAbstractClass();

        $step->expects($this->once())->method('toArray')->willReturn([]);
        $step->expects($this->any())->method('getCleanFilterWhiteList')->willReturn([]);
        $step->expects($this->any())->method('clean')->with([])->willReturn([]);
        $filter->expects($this->once())->method('init');
        $filter->expects($this->once())->method('setData');
        $filter->expects($this->once())->method('isValid')->willReturn(__METHOD__);

        $this->assertEquals(__METHOD__, $step->validate());
    }

    /**
     * @dataProvider dataToClean
     *
     * @param array $data
     * @param array $expected
     * @param array $blackList
     *
     * @throws \Exception
     */
    public function testClean($data, $expected, $blackList)
    {
        $filter = XMock::of(InputFilter::class);
        $step = $this->getMockBuilder(AbstractRegistrationStep::class)
            ->setConstructorArgs([
                XMock::of(RegistrationSessionService::class),
                $filter,
            ])
            ->getMockForAbstractClass();

        $step->expects($this->once())->method('getCleanFilterWhiteList')->willReturn($blackList);

        $actual = $step->clean($data);

        $this->assertSame($expected, $actual);
    }

    /**
     * @return array
     */
    public function dataToClean()
    {
        return [
            [[self::STEP_FIELD => '   testword   '], [self::STEP_FIELD => 'testword' ], [self::STEP_FIELD]],
            [[self::STEP_FIELD => 'testword   '], [self::STEP_FIELD => 'testword' ], [self::STEP_FIELD]],
            [[self::STEP_FIELD => '  testword'], [self::STEP_FIELD => 'testword' ], [self::STEP_FIELD]],
            [['password'       => '  testword'], ['password' => '  testword' ], [self::STEP_FIELD]], // shouldn't change as password isn't in getCleanFilterWhitelist
            [['password'       => '  testword'], ['password' => '  testword' ], []],
        ];
    }
}
