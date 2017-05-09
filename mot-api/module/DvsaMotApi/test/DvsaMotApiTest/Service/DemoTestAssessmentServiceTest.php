<?php

namespace DvsaMotApiTest\Service;

use DvsaCommon\Auth\MotAuthorisationServiceInterface;
use DvsaCommon\Enum\AuthorisationForTestingMotStatusCode;
use DvsaCommon\Enum\VehicleClassGroupCode;
use DvsaCommon\Model\VehicleClassGroup;
use DvsaCommon\Utility\ArrayUtils;
use DvsaCommonApi\Service\Exception\BadRequestException;
use DvsaCommonTest\Date\TestDateTimeHolder;
use DvsaCommonTest\TestUtils\MethodSpy;
use DvsaCommonTest\TestUtils\XMock;
use DvsaEntities\Entity\AuthorisationForTestingMot;
use DvsaEntities\Entity\AuthorisationForTestingMotStatus;
use DvsaEntities\Entity\Person;
use DvsaEntities\Entity\VehicleClass;
use DvsaEntities\Repository\AuthorisationForTestingMotRepository;
use DvsaEntities\Repository\AuthorisationForTestingMotStatusRepository;
use DvsaEntities\Repository\PersonRepository;
use DvsaMotApi\Helper\TesterQualificationStatusChangeEventHelper;
use DvsaMotApi\Service\DemoTestAssessmentService;
use NotificationApi\Dto\Notification;
use NotificationApi\Service\NotificationService;

/**
 * Class CertificateCreationServiceTest.
 */
class DemoTestAssessmentServiceTest extends \PHPUnit_Framework_TestCase
{
    /** @var DemoTestAssessmentService */
    private $service;

    /** @var MotAuthorisationServiceInterface | \PHPUnit_Framework_MockObject_MockObject */
    private $authorisationService;

    /** @var NotificationService | \PHPUnit_Framework_MockObject_MockObject */
    private $notificationService;

    /** @var PersonRepository | \PHPUnit_Framework_MockObject_MockObject */
    private $personRepository;

    /** @var AuthorisationForTestingMotRepository | \PHPUnit_Framework_MockObject_MockObject */
    private $authorisationForTestingMotRepository;

    /** @var AuthorisationForTestingMotStatusRepository | \PHPUnit_Framework_MockObject_MockObject */
    private $authorisationForTestingMotStatusRepository;

    /** @var TesterQualificationStatusChangeEventHelper | \PHPUnit_Framework_MockObject_MockObject */
    private $testerQualificationStatusChangeEventHelper;

    /**
     * @var TestDateTimeHolder
     */
    private $timeHolder;

    /**
     * @var Person
     */
    private $tester = 1010;

    public function setup()
    {
        $this->authorisationService = XMock::of(MotAuthorisationServiceInterface::class);
        $this->notificationService = XMock::of(NotificationService::class);
        $this->personRepository = XMock::of(PersonRepository::class);
        $this->authorisationForTestingMotRepository = XMock::of(AuthorisationForTestingMotRepository::class);
        $this->authorisationForTestingMotStatusRepository = XMock::of(AuthorisationForTestingMotStatusRepository::class);
        $this->testerQualificationStatusChangeEventHelper = XMock::of(TesterQualificationStatusChangeEventHelper::class);
        $this->timeHolder = new TestDateTimeHolder(new \DateTime());

        $this->tester = new Person();
        $this->tester->setId(1010);
        $this->personRepository->expects($this->any())
            ->method('find')
            ->with($this->tester->getId())
            ->willReturn($this->tester);

        $this->authorisationForTestingMotStatusRepository->expects($this->any())
            ->method('getByCode')
            ->with(AuthorisationForTestingMotStatusCode::QUALIFIED)
            ->willReturn((new AuthorisationForTestingMotStatus())->setCode(AuthorisationForTestingMotStatusCode::QUALIFIED));

        $this->service = new DemoTestAssessmentService(
            $this->authorisationService,
            $this->notificationService,
            $this->personRepository,
            $this->authorisationForTestingMotRepository,
            $this->authorisationForTestingMotStatusRepository,
            $this->testerQualificationStatusChangeEventHelper,
            $this->timeHolder
        );
    }

    /**
     * @dataProvider vehicleGroupsDataProvider
     *
     * @param $vehicleGroup
     */
    public function testAssessDemoTestQualifiesTester($vehicleGroup)
    {
        $this->setAuthorisationInGroupAToStatus(AuthorisationForTestingMotStatusCode::DEMO_TEST_NEEDED, $vehicleGroup);

        // GIVEN I have a tester I want to qualify in a group
        $data = ['testerId' => $this->tester->getId(), 'vehicleClassGroup' => $vehicleGroup];

        // WHEN i assess his demo test
        $this->service->create($data);

        // THEN tester is qualified in his group
        /** @var AuthorisationForTestingMot[] $authorisationsInGroup */
        $authorisationsInGroup = $this->getAuthorisationsInGroup($vehicleGroup);

        foreach ($authorisationsInGroup as $authorisation) {
            $this->assertEquals(AuthorisationForTestingMotStatusCode::QUALIFIED,
                $authorisation->getStatus()->getCode(),
                "The tester's authorisation status was supposed to be qualified"
            );
        }
    }

    /**
     * @dataProvider vehicleGroupsDataProvider
     *
     * @param $vehicleGroup
     */
    public function testAssessDemoTestSendsEvent($vehicleGroup)
    {
        $this->setAuthorisationInGroupAToStatus(AuthorisationForTestingMotStatusCode::DEMO_TEST_NEEDED, $vehicleGroup);
        $eventSpy = new MethodSpy($this->testerQualificationStatusChangeEventHelper, 'create');

        // GIVEN I have a tester I want to qualify in a group
        $data = ['testerId' => $this->tester->getId(), 'vehicleClassGroup' => $vehicleGroup];

        // WHEN i assess his demo test
        $this->service->create($data);

        // THEN an event is recorded
        $this->assertEquals(1, $eventSpy->invocationCount(),
            'The event service was not called, so event was not sent');

        // for the tester
        $this->assertSame($this->tester, $eventSpy->paramsForLastInvocation()[0],
            'The wrong person received event');

        // with the group in which he is being qualified for
        $this->assertSame($vehicleGroup, $eventSpy->paramsForLastInvocation()[1],
            'Wrong vehicle group was mentioned in teh event');
    }

    /**
     * @dataProvider vehicleGroupsDataProvider
     *
     * @param $vehicleGroup
     */
    public function testAssessDemoTestSendsNotification($vehicleGroup)
    {
        $this->setAuthorisationInGroupAToStatus(AuthorisationForTestingMotStatusCode::DEMO_TEST_NEEDED, $vehicleGroup);
        $notificationSpy = new MethodSpy($this->notificationService, 'add');

        // GIVEN I have a tester I want to qualify in a group
        $data = ['testerId' => $this->tester->getId(), 'vehicleClassGroup' => $vehicleGroup];

        // WHEN i assess his demo test
        $this->service->create($data);

        // THEN Notification is sent
        $this->assertEquals(1, $notificationSpy->invocationCount(),
            "The 'add' method of notification service was not called");

        $notification = $notificationSpy->paramsForLastInvocation()[0];

        // with TEMPLATE_TESTER_QUALIFICATION_STATUS template
        $this->assertEquals(Notification::TEMPLATE_TESTER_QUALIFICATION_STATUS, $notification['template'],
            'Wrong template was chosen for the notification');

        // addressed to the tester
        $this->assertEquals($this->tester->getId(), $notification['recipient'],
            'It was addressed to the wrong person');

        // stating he has been qualified for the correct group
        $this->assertEquals($vehicleGroup, $notification['fields']['vehicle_group'],
            'Wrong vehicle group is displayed in the notification');
    }

    /**
     * @dataProvider vehicleGroupsDataProvider
     *
     * @param $vehicleGroup
     */
    public function testAssessDemoTestFailsWhenTesterIsNotDemoRequired($vehicleGroup)
    {
        $this->setAuthorisationInGroupAToStatus(AuthorisationForTestingMotStatusCode::INITIAL_TRAINING_NEEDED, $vehicleGroup);
        $notificationSpy = new MethodSpy($this->notificationService, 'add');
        $eventSpy = new MethodSpy($this->testerQualificationStatusChangeEventHelper, 'create');

        // GIVEN I have a tester I want to qualify in a group
        $data = ['testerId' => $this->tester->getId(), 'vehicleClassGroup' => $vehicleGroup];

        $errorMessage = 'No exception';
        try {
            // WHEN i assess his demo test
            $this->service->create($data);
        } catch (BadRequestException $e) {
            $errorMessage = $e->getErrors()[0]['message'];
        }

        // THEN Exception is raised
        $this->assertEquals(DemoTestAssessmentService::ERROR_NOT_DEMO_REQUIRED_STATUS, $errorMessage,
            "We've expected an exception saying that the status is wrong");

        // AND notification is not sent
        $this->assertEquals(0, $notificationSpy->invocationCount(),
            'Notification service was called, but the notification was not supposed to be sent');

        // AND tester is still in same status in his group
        /** @var AuthorisationForTestingMot[] $authorisationsInGroup */
        $authorisationsInGroup = $this->getAuthorisationsInGroup($vehicleGroup);

        foreach ($authorisationsInGroup as $authorisation) {
            $this->assertEquals(AuthorisationForTestingMotStatusCode::INITIAL_TRAINING_NEEDED,
                $authorisation->getStatus()->getCode(),
                'The authorisation status was supposed to not change');
        }

        // AND the event is not recorded
        $this->assertEquals(0, $eventSpy->invocationCount(),
            "An event was sent even though it shouldn't");
    }

    public function testAssessDemoTestInputValidation()
    {
        // GIVEN I have invalid parameters for tester's demo assessment
        $data = ['testerId' => $this->tester->getId(), 'vehicleClassGroup' => 'C'];

        $errorMessage = 'No exception';
        try {
            // WHEN i assess his demo test
            $this->service->create($data);
        } catch (BadRequestException $e) {
            $errorMessage = $e->getErrors()[0]['message'];
        }

        // THEN Exception is raised
        $this->assertEquals("Unknown group 'C'", $errorMessage,
            'Validation message was supposed to be thrown');
    }

    /**
     * @param $vehicleGroup
     *
     * @return AuthorisationForTestingMot[]
     */
    public function getAuthorisationsInGroup($vehicleGroup)
    {
        return ArrayUtils::filter(
            $this->tester->getAuthorisationsForTestingMot(),
            function (AuthorisationForTestingMot $authorisation) use ($vehicleGroup) {
                return VehicleClassGroup::isGroup($authorisation->getVehicleClass()->getCode(), $vehicleGroup);
            }
        );
    }

    public function vehicleGroupsDataProvider()
    {
        return [
            [VehicleClassGroupCode::BIKES],
            [VehicleClassGroupCode::CARS_ETC],
        ];
    }

    private function setAuthorisationInGroupAToStatus($statusCode, $vehicleGroupCode)
    {
        $classesInGroup = VehicleClassGroup::getClassesForGroup($vehicleGroupCode);

        $status = (new AuthorisationForTestingMotStatus())->setCode($statusCode);

        foreach ($classesInGroup as $classCode) {
            $class = new VehicleClass($classCode);

            $this->tester->addAuthorisationForTestingMot(
                (new AuthorisationForTestingMot())
                    ->setStatus($status)
                    ->setVehicleClass($class)
            );
        }
    }
}
