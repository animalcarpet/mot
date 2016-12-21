<?php

namespace DvsaMotTestTest\Service;

use DvsaCommonTest\TestUtils\XMock;
use DvsaMotTest\Service\StartTestChangeService;
use DvsaMotTest\Service\StartTestSessionService;
use Zend\View\Helper\Url;

class StartTestChangeServiceTest extends \PHPUnit_Framework_TestCase
{
    const OBFUSCATED_VEHICLE_ID = '2fe';

    private $startTestSessionService;

    private $url;

    public function setUp()
    {
        parent::setUp();

        $this->startTestSessionService = XMock::of(StartTestSessionService::class);
        $this->url = XMock::of(Url::class);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Changes are not stored in session
     */
    public function testUpdateChangedValueStatus_changesNotInSession_shouldThrowException()
    {
        foreach ($this->buildService()->getAllowedChanges() as $allowedChange) {
            $this->buildService()->updateChangedValueStatus(
                $allowedChange, true
            );
        }
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Change value: fakeChange is not an allowed change
     */
    public function testUpdateChangedValueStatus_notAnAllowedChange_shouldThrowException()
    {
        $this->startTestSessionService
            ->expects($this->once())
            ->method('load')
            ->with(StartTestSessionService::UNIQUE_KEY)
            ->willReturn($this->mockLoadedValues());
        $this->buildService()->updateChangedValueStatus('fakeChange', true);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Changed value status must be a boolean
     */
    public function testUpdateChangedValueStatus_notABooleanChange_shouldThrowException()
    {
        $this->startTestSessionService
            ->expects($this->once())
            ->method('load')
            ->with(StartTestSessionService::UNIQUE_KEY)
            ->willReturn($this->mockLoadedValues());
        foreach ($this->buildService()->getAllowedChanges() as $allowedChange) {
            $this->buildService()->updateChangedValueStatus(
                $allowedChange, ''
            );
        }
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Change value fakeChange is not a valid change.
     */
    public function testGetChangedValue_notAnAllowedChange_shouldThrowException()
    {
        $this->buildService()->getChangedValue('fakeChange');
    }

    public function testGetChangedValue_correctAllowedChange_shouldReturnChangedValue()
    {
        $this->startTestSessionService
            ->expects($this->once())
            ->method('load')
            ->with(StartTestSessionService::UNIQUE_KEY)
            ->willReturn($this->mockLoadedValues());
        $actual = $this->buildService()->getChangedValue(StartTestChangeService::CHANGE_CLASS);
        $this->assertSame('4', $actual);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Change value fakeChange is not an allowed change.
     */
    public function testSaveChange_notAnAllowedChange_shouldThrowException()
    {
        $this->buildService()->saveChange('fakeChange', []);
    }

    public function testSaveChange_correctAllowedChange_shouldSaveTheChange()
    {
        $this->startTestSessionService
            ->expects($this->any())
            ->method('load')
            ->with(StartTestSessionService::UNIQUE_KEY)
            ->willReturn($this->mockLoadedValues());
        $this->startTestSessionService
            ->expects($this->once())
            ->method('save')
            ->with(StartTestSessionService::UNIQUE_KEY, $this->mockLoadedValues());
        $this->buildService()->saveChange(StartTestChangeService::CHANGE_COLOUR, ['colour' => [
                'primaryColour' => 'K',
                'secondaryColour' => 'G',
            ],
        ]);
        $this->assertSame('K', $this->buildService()->getChangedValue(StartTestChangeService::CHANGE_COLOUR)['colour']['primaryColour']);
        $this->assertSame('G', $this->buildService()->getChangedValue(StartTestChangeService::CHANGE_COLOUR)['colour']['secondaryColour']);
    }

    public function testLoadStepsInToSession_shouldOnlyContainAllowedChanges()
    {
        $this->startTestSessionService
            ->expects($this->once())
            ->method('clear');
        $this->startTestSessionService
            ->expects($this->once())
            ->method('save')
            ->with(StartTestSessionService::UNIQUE_KEY, $this->mockAllowedChanges());
        $this->buildService()->loadAllowedChangesIntoSession();
    }

    private function mockAllowedChanges()
    {
        return [
            'allowed_changes' => [
                'class' => false,
                'colour' => false,
                'country' => false,
                'engine' => false,
                'make' => false,
                'model' => false,
                'noRegistration' => false,
                'source' => false,
                'url' => false,
            ],
        ];
    }

    private function mockLoadedValues($classChanged = false)
    {
        return [
            'allowed_changes' => [
                'class' => $classChanged,
                'colour' => false,
                'country' => false,
                'engine' => false,
                'make' => false,
                'model' => false,
                'noRegistration' => false,
                'source' => false,
                'url' => false,
            ],
            'user_data' => [
                'noRegistration' => [
                    'noRegistration' => '0',
                ],
                'source' => [
                    'source' => '1',
                ],
                'class' => '4',
                'colour' => [
                    'colour' => [
                        'primaryColour' => 'K',
                        'secondaryColour' => 'G',
                     ],
                ],
                'url' => 'test',
            ],
        ];
    }

    private function buildService()
    {
        return new StartTestChangeService(
            $this->startTestSessionService,
            $this->url
        );
    }
}