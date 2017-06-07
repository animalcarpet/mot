<?php

namespace DvsaMotTestTest\Service;

use Core\Service\MotFrontendIdentityProviderInterface;
use Dvsa\Mot\Frontend\AuthenticationModule\Model\Identity;
use Dvsa\Mot\Frontend\AuthenticationModule\Model\VehicleTestingStation;
use DvsaCommonTest\TestUtils\XMock;
use DvsaMotTest\Service\AuthorisedClassesService;
use DvsaMotTest\Service\StartTestChangeService;
use DvsaMotTest\Service\StartTestSessionService;
use Zend\View\Helper\Url;

class StartTestChangeServiceTest extends \PHPUnit_Framework_TestCase
{
    const OBFUSCATED_VEHICLE_ID = '2fe';

    private $startTestSessionService;

    private $url;

    /** @var MotFrontendIdentityProviderInterface */
    private $identityProvider;

    /** @var AuthorisedClassesService */
    private $authorisedClassesService;

    public function setUp()
    {
        parent::setUp();

        $this->startTestSessionService = XMock::of(StartTestSessionService::class);
        $this->url = XMock::of(Url::class);
        $this->authorisedClassesService = XMock::of(AuthorisedClassesService::class);
        $this->identityProvider = XMock::of(MotFrontendIdentityProviderInterface::class);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Changes are not stored in session
     */
    public function testUpdateChangedValueStatus_changesNotInSession_shouldThrowException()
    {
        foreach ($this->getVehicleChanges() as $vehicleChange) {
            $this->buildService()->updateChangedValueStatus(
                $vehicleChange, true
            );
        }
    }

    public function testUpdateChangedValueStatus_changesInSession_shouldMakeTheChange()
    {
        $this->startTestSessionService
            ->expects($this->at(0))
            ->method('load')
            ->with(StartTestSessionService::UNIQUE_KEY)
            ->willReturn($this->mockLoadedValues());

        $this->startTestSessionService
            ->expects($this->at(1))
            ->method('save')
            ->with(StartTestSessionService::UNIQUE_KEY,
                [
                    StartTestSessionService::VEHICLE_CHANGE_STATUS => [
                        'engine' => true,
                        'class' => false,
                        'colour' => false,
                        'country' => false,
                        'make' => false,
                        'model' => false,
                        'noRegistration' => false,
                        'source' => false,
                        'url' => false,
                        'normalOrRetest' => false,
                    ],
                    StartTestSessionService::USER_DATA => [
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
                        'normalOrRetest' => [
                            'normalOrRetest' => false,
                        ],
                    ],
                ]
            );

        $this->startTestSessionService
            ->expects($this->at(2))
            ->method('load')
            ->with(StartTestSessionService::UNIQUE_KEY)
            ->willReturn($this->mockLoadedValues(false, false, false, 1, false, true));

        $this->buildService()->updateChangedValueStatus('engine', true);
        $this->assertTrue($this->buildService()->isValueChanged(StartTestChangeService::CHANGE_ENGINE));
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
        foreach ($this->getVehicleChanges() as $vehicleChange) {
            $this->buildService()->updateChangedValueStatus(
                $vehicleChange, ''
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
            ->with(StartTestSessionService::UNIQUE_KEY, $this->mockVehicleChangeStatus());
        $this->buildService()->loadAllowedChangesIntoSession();
    }

    public function testIsValueChanged_emptyAllowedChanges_returnFalse()
    {
        foreach ($this->getVehicleChanges() as $vehicleChange) {
            $actual = $this->buildService()->isValueChanged($vehicleChange);
            $this->assertSame(false, $actual);
        }
    }

    public function testIsValueChanged_notACorrectChange_returnFalse()
    {
        $this->startTestSessionService
            ->expects($this->once())
            ->method('load')
            ->with(StartTestSessionService::UNIQUE_KEY)
            ->willReturn($this->mockLoadedValues());
        $actual = $this->buildService()->isValueChanged('fakeChange');
        $this->assertSame(false, $actual);
    }

    public function testIsValueChanged_withAValidChange_returnChangedValue()
    {
        $this->startTestSessionService
            ->expects($this->once())
            ->method('load')
            ->with(StartTestSessionService::UNIQUE_KEY)
            ->willReturn($this->mockLoadedValues(true));
        $actual = $this->buildService()->isValueChanged(StartTestChangeService::CHANGE_CLASS);
        $this->assertSame(true, $actual);
    }

    public function testIsMakeAndModelChanged_bothChanged_returnTrue()
    {
        $this->startTestSessionService
            ->expects($this->any())
            ->method('load')
            ->willReturn($this->mockLoadedValues(false, true, true));

        $actual = $this->buildService()->isMakeAndModelChanged();
        $this->assertTrue($actual);
    }

    public function testIsMakeAndModelChanged_neitherChanged_returnFalse()
    {
        $this->startTestSessionService
            ->expects($this->any())
            ->method('load')
            ->willReturn($this->mockLoadedValues(false, false, false));

        $actual = $this->buildService()->isMakeAndModelChanged();
        $this->assertFalse($actual);
    }

    public function testIsMakeAndModelChanged_onlyMakeChanged_returnFalse()
    {
        $this->startTestSessionService
            ->expects($this->any())
            ->method('load')
            ->willReturn($this->mockLoadedValues(false, true, false));

        $actual = $this->buildService()->isMakeAndModelChanged();
        $this->assertFalse($actual);
    }

    public function testIsDvlaVehicle_whenNotDvlaVehicle_shouldReturnFalse()
    {
        $this->startTestSessionService
            ->expects($this->once())
            ->method('load')
            ->willReturn($this->mockLoadedValues(false, false, false, 1));
        $result = $this->buildService()->isDvlaVehicle();
        $this->assertFalse($result);
    }

    public function testIsDvlaVehicle_whenDvlaVehicle_shouldReturnTrue()
    {
        $this->startTestSessionService
            ->expects($this->once())
            ->method('load')
            ->willReturn($this->mockLoadedValues(false, false, false, 2));
        $result = $this->buildService()->isDvlaVehicle();
        $this->assertTrue($result);
    }

    public function testIsAuthorisedToTestClass_whenOnlyTesterCanTest_shouldReturnFalse()
    {
        $this->startTestSessionService
            ->expects($this->any())
            ->method('load')
            ->willReturn($this->mockLoadedValues(false, false, false, 1, true));

        $this->identityProvider
            ->expects($this->once())
            ->method('getIdentity')
            ->willReturn((new Identity())
                ->setUserId(1)
                ->setCurrentVts((new VehicleTestingStation())->setVtsId(1))
            );

        $this->authorisedClassesService
            ->expects($this->exactly(1))
            ->method('getCombinedAuthorisedClassesForPersonAndVts')
            ->with(1, 1)
            ->willReturn($this->mockCombinedAuthorisedClasses($this->allTesterApprovedClasses(), []));

        $result = $this->buildService()->isAuthorisedToTestClass(2);
        $this->assertFalse($result);
    }

    public function testIsAuthorisedToTestClass_whenOnlyVtsCanTest_shouldReturnFalse()
    {
        $this->startTestSessionService
            ->expects($this->any())
            ->method('load')
            ->willReturn($this->mockLoadedValues(false, false, false, 1, true));

        $this->identityProvider
            ->expects($this->once())
            ->method('getIdentity')
            ->willReturn((new Identity())
                ->setUserId(1)
                ->setCurrentVts((new VehicleTestingStation())->setVtsId(1))
            );

        $this->authorisedClassesService
            ->expects($this->exactly(1))
            ->method('getCombinedAuthorisedClassesForPersonAndVts')
            ->with(1, 1)
            ->willReturn($this->mockCombinedAuthorisedClasses([], $this->allVtsApprovedClasses()));

        $result = $this->buildService()->isAuthorisedToTestClass(2);
        $this->assertFalse($result);
    }

    public function testIsAuthorisedToTestClass_whenNeitherVtsOrTesterCanTest_shouldReturnFalse()
    {
        $this->startTestSessionService
            ->expects($this->any())
            ->method('load')
            ->willReturn($this->mockLoadedValues(false, false, false, 1, true));

        $this->identityProvider
            ->expects($this->once())
            ->method('getIdentity')
            ->willReturn((new Identity())
                ->setUserId(1)
                ->setCurrentVts((new VehicleTestingStation())->setVtsId(1))
            );

        $this->authorisedClassesService
            ->expects($this->exactly(1))
            ->method('getCombinedAuthorisedClassesForPersonAndVts')
            ->with(1, 1)
            ->willReturn($this->mockCombinedAuthorisedClasses([], []));

        $result = $this->buildService()->isAuthorisedToTestClass(2);
        $this->assertFalse($result);
    }

    public function testIsAuthorisedToTestClass_whenBothVtsAndTesterCanTest_shouldReturnTrue()
    {
        $this->startTestSessionService
            ->expects($this->any())
            ->method('load')
            ->willReturn($this->mockLoadedValues(false, false, false, 1, true));

        $this->identityProvider
            ->expects($this->once())
            ->method('getIdentity')
            ->willReturn((new Identity())
                ->setUserId(1)
                ->setCurrentVts((new VehicleTestingStation())->setVtsId(1))
            );

        $this->authorisedClassesService
            ->expects($this->exactly(1))
            ->method('getCombinedAuthorisedClassesForPersonAndVts')
            ->with(1, 1)
            ->willReturn($this->mockCombinedAuthorisedClasses($this->allTesterApprovedClasses(), $this->allVtsApprovedClasses()));

        $result = $this->buildService()->isAuthorisedToTestClass(2);
        $this->assertTrue($result);
    }

    private function mockCombinedAuthorisedClasses(array $testerApprovedClasses, array $vtsApprovedClasses)
    {
        return [
            AuthorisedClassesService::KEY_FOR_PERSON_APPROVED_CLASSES => $testerApprovedClasses,
            AuthorisedClassesService::KEY_FOR_VTS_APPROVED_CLASSES => $vtsApprovedClasses,
        ];
    }

    private function allTesterApprovedClasses()
    {
        return [
            '1',
            '2',
            '3',
            '4',
            '5',
            '7',
        ];
    }

    private function allVtsApprovedClasses()
    {
        return [
            '1',
            '2',
            '3',
            '4',
            '5',
            '7',
        ];
    }

    private function mockVehicleChangeStatus()
    {
        return [
            StartTestSessionService::VEHICLE_CHANGE_STATUS => [
                'class' => false,
                'colour' => false,
                'country' => false,
                'engine' => false,
                'make' => false,
                'model' => false,
                'noRegistration' => false,
                'source' => false,
                'url' => false,
                'normalOrRetest' => false,
            ],
        ];
    }

    /**
     * @param bool $classChanged
     * @param bool $makeChanged
     * @param bool $modelChanged
     * @param int  $source
     * @param bool $normalOrRetest
     *
     * @return array
     */
    private function mockLoadedValues($classChanged = false, $makeChanged = false, $modelChanged = false, $source = 1, $normalOrRetest = false, $engine = false)
    {
        return [
            StartTestSessionService::VEHICLE_CHANGE_STATUS => [
                'class' => $classChanged,
                'colour' => false,
                'country' => false,
                'engine' => $engine,
                'make' => $makeChanged,
                'model' => $modelChanged,
                'noRegistration' => false,
                'source' => false,
                'url' => false,
                'normalOrRetest' => $normalOrRetest,
            ],
            StartTestSessionService::USER_DATA => [
                'noRegistration' => [
                    'noRegistration' => '0',
                ],
                'source' => [
                    'source' => $source,
                ],
                'class' => '4',
                'colour' => [
                    'colour' => [
                        'primaryColour' => 'K',
                        'secondaryColour' => 'G',
                     ],
                ],
                'url' => 'test',
                'normalOrRetest' => [
                    'normalOrRetest' => $normalOrRetest,
                ],
            ],
        ];
    }

    private function getVehicleChanges()
    {
        return [
            StartTestChangeService::CHANGE_CLASS,
            StartTestChangeService::CHANGE_COLOUR,
            StartTestChangeService::CHANGE_COUNTRY,
            StartTestChangeService::CHANGE_ENGINE,
            StartTestChangeService::CHANGE_MAKE,
            StartTestChangeService::CHANGE_MODEL,
            StartTestChangeService::NO_REGISTRATION,
            StartTestChangeService::SOURCE,
            StartTestChangeService::URL,
            StartTestChangeService::NORMAL_OR_RETEST,
        ];
    }

    private function buildService()
    {
        return new StartTestChangeService(
            $this->startTestSessionService,
            $this->url,
            $this->identityProvider,
            $this->authorisedClassesService
        );
    }
}
