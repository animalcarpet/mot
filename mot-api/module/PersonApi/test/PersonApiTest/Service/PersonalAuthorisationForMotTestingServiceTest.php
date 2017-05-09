<?php

namespace UserApiTest\Service;

use Doctrine\ORM\EntityManager;
use DvsaCommon\Enum\AuthorisationForTestingMotStatusCode;
use DvsaCommon\Enum\VehicleClassCode;
use DvsaCommonApi\Service\Exception\BadRequestException;
use DvsaCommonApiTest\Service\AbstractServiceTestCase;
use DvsaCommonTest\TestUtils\XMock;
use DvsaEntities\Entity\AuthorisationForTestingMot;
use DvsaEntities\Entity\AuthorisationForTestingMotStatus;
use DvsaEntities\Entity\Person;
use DvsaEntities\Entity\VehicleClass;
use DvsaEventApi\Service\EventService;
use NotificationApi\Service\NotificationService;
use PersonApi\Service\PersonalAuthorisationForMotTestingService;
use PersonApi\Service\PersonService;
use PersonApi\Service\Validator\PersonalAuthorisationForMotTestingValidator;
use DvsaEntities\Repository\AuthorisationForTestingMotStatusRepository;
use DvsaEntities\Repository\VehicleClassRepository;
use Zend\Authentication\AuthenticationService;

/**
 * Unit tests for PersonalAuthorisationForMotTestingService.
 */
class PersonalAuthorisationForMotTestingServiceTest extends AbstractServiceTestCase
{
    const PERSON_ID = 1;

    private $entityManager;
    private $notificationService;
    private $personalAuthMotTestValidator;
    private $authorisationService;
    private $eventService;
    private $personService;
    private $authorisationForTestingMotStatusRepository;
    private $vehicleClassRepository;
    private $authenticationService;
    /** @var Person */
    private $person;

    public function setUp()
    {
        $this->entityManager = XMock::of(EntityManager::class);
        $this->notificationService = XMock::of(NotificationService::class);
        $this->personalAuthMotTestValidator = new PersonalAuthorisationForMotTestingValidator();
        $this->authorisationService = $this->getMockAuthorizationService(false);
        $this->eventService = XMock::of(EventService::class);

        $person = new Person();
        $person->setId(self::PERSON_ID);
        $person->setUsername('tester1');
        $this->person = $person;

        $this->personService = XMock::of(PersonService::class);
        $this->personService->expects($this->any())
             ->method('getPersonById')
             ->with(self::PERSON_ID)
             ->willReturn($this->person);

        $initialTrainingCode = new AuthorisationForTestingMotStatus();
        $initialTrainingCode->setId(10)->setName('Initial Training Needed')
                            ->setCode(AuthorisationForTestingMotStatusCode::INITIAL_TRAINING_NEEDED);

        $unknownCode = new AuthorisationForTestingMotStatus();
        $unknownCode->setId(9)->setName('Unknown')
                    ->setCode(AuthorisationForTestingMotStatusCode::UNKNOWN);

        $this->authorisationForTestingMotStatusRepository = XMock::of(AuthorisationForTestingMotStatusRepository::class);
        $this->authorisationForTestingMotStatusRepository
            ->expects($this->any())
            ->method('getByCode')
            ->will(
                $this->returnValueMap(
                    [
                        [
                            AuthorisationForTestingMotStatusCode::INITIAL_TRAINING_NEEDED, $initialTrainingCode,
                        ],
                        [
                            AuthorisationForTestingMotStatusCode::UNKNOWN, $unknownCode,
                        ],
                    ]
                )
            );

        $this->vehicleClassRepository = $this->getVehicleClassRepositoryWithMocks();

        $authorisedUser = new Person();
        $authorisedUser->setId(100);
        $authorisedUser->setUsername('I-am-admin');

        $this->authenticationService = XMock::of(AuthenticationService::class);
        $this->authenticationService->expects($this->any())
                                    ->method('getIdentity')
                                    ->willReturn($authorisedUser);
    }

    public function testCanGetAuthorisationForTestingMotList()
    {
        // When: a person has class 1 and class 2 as qualified
        $this->setPersonAuthorisationForTesting(
            [
                (new VehicleClass(1, 1))->setId(1),
                (new VehicleClass(2, 2))->setId(2),
            ]
        );

        // And: I request to get the personalTestingAuthorisations
        $response = $this->getService()->getPersonalTestingAuthorisation(self::PERSON_ID);

        // Then: I should receive an array of Mot Authorisation Of Classes with what is expected
        $this->assertNotNull($response);

        $authForTesting = $response->toArray();

        $this->assertEquals(
            AuthorisationForTestingMotStatusCode::QUALIFIED,
            $authForTesting['class1']
        );
        $this->assertEquals(
            AuthorisationForTestingMotStatusCode::QUALIFIED,
            $authForTesting['class2']
        );
    }

    public function testPersonWithNoAuthorisationForTestingMotListWillHaveBlankResponses()
    {
        // When: a person has no classes or PersonAuthorisationForTesting
        // And: I request to get the personalTestingAuthorisations
        $response = $this->getService()->getPersonalTestingAuthorisation(self::PERSON_ID);

        // Then: I should receive an array of Mot Authorisation Of Classes with what is expected
        $this->assertNotNull($response);

        $authForTesting = $response->toArray();

        $this->assertNull($authForTesting['class1']);
        $this->assertNull($authForTesting['class2']);
        $this->assertNull($authForTesting['class3']);
        $this->assertNull($authForTesting['class4']);
        $this->assertNull($authForTesting['class5']);
        $this->assertNull($authForTesting['class7']);
    }

    public function testUpdatePersonAuthorisationForTestingMotForGroupAAsQualified()
    {
        // When: a person has all classes as for Group A (1 and 2)
        $vehicleClasses = [];
        for ($i = 1; $i <= 7; ++$i) {
            if ($i == 6) {
                continue;
            }
            $vehicleClasses[] = (new VehicleClass($i, $i))->setId($i)->setCode($i);
        }

        $this->setPersonAuthorisationForTesting($vehicleClasses);

        $response = $this->getService()->getPersonalTestingAuthorisation(self::PERSON_ID);
        $authForTesting = $response->toArray();

        $this->assertEquals(
            AuthorisationForTestingMotStatusCode::QUALIFIED,
            $authForTesting['class1']
        );
        $this->assertEquals(
            AuthorisationForTestingMotStatusCode::QUALIFIED,
            $authForTesting['class2']
        );

        $response = null;

        // And: I request to update status of Group A for this user to INITIAL_TRAINING_NEEDED
        $data['group'] = 1;
        $data['result'] = AuthorisationForTestingMotStatusCode::INITIAL_TRAINING_NEEDED;

        $response = $this->getService()->updatePersonalTestingAuthorisationGroup(self::PERSON_ID, $data);

        // Then: I should receive an array of Mot Authorisation Of Classes with what is expected
        $this->assertNotNull($response);

        $authForTesting = $response->toArray();

        $this->assertEquals(
            AuthorisationForTestingMotStatusCode::INITIAL_TRAINING_NEEDED,
            $authForTesting['class1']
        );
        $this->assertEquals(
            AuthorisationForTestingMotStatusCode::INITIAL_TRAINING_NEEDED,
            $authForTesting['class2']
        );
    }

    public function testUpdatePersonAuthorisationForTestingMotForGroupBAsQualified()
    {
        // When: a person has all classes as for Group A (1 and 2)
        $vehicleClasses = [];
        for ($i = 1; $i <= 7; ++$i) {
            if ($i == 6) {
                continue;
            }
            $vehicleClasses[] = (new VehicleClass($i, $i))->setId($i)->setCode($i);
        }

        $this->setPersonAuthorisationForTesting($vehicleClasses);

        $response = $this->getService()->getPersonalTestingAuthorisation(self::PERSON_ID);
        $authForTesting = $response->toArray();

        $this->assertEquals(
            AuthorisationForTestingMotStatusCode::QUALIFIED,
            $authForTesting['class3']
        );
        $this->assertEquals(
            AuthorisationForTestingMotStatusCode::QUALIFIED,
            $authForTesting['class4']
        );
        $this->assertEquals(
            AuthorisationForTestingMotStatusCode::QUALIFIED,
            $authForTesting['class5']
        );
        $this->assertEquals(
            AuthorisationForTestingMotStatusCode::QUALIFIED,
            $authForTesting['class7']
        );

        $response = null;

        // And: I request to update status of Group B for this user to INITIAL_TRAINING_NEEDED
        $data['group'] = 2;
        $data['result'] = AuthorisationForTestingMotStatusCode::INITIAL_TRAINING_NEEDED;

        $response = $this->getService()->updatePersonalTestingAuthorisationGroup(self::PERSON_ID, $data);

        // Then: I should receive an array of Mot Authorisation Of Classes with what is expected
        $this->assertNotNull($response);

        $authForTesting = $response->toArray();

        $this->assertEquals(
            AuthorisationForTestingMotStatusCode::INITIAL_TRAINING_NEEDED,
            $authForTesting['class3']
        );
        $this->assertEquals(
            AuthorisationForTestingMotStatusCode::INITIAL_TRAINING_NEEDED,
            $authForTesting['class4']
        );
        $this->assertEquals(
            AuthorisationForTestingMotStatusCode::INITIAL_TRAINING_NEEDED,
            $authForTesting['class5']
        );
        $this->assertEquals(
            AuthorisationForTestingMotStatusCode::INITIAL_TRAINING_NEEDED,
            $authForTesting['class7']
        );
    }

    /**
     * Negative Scenario - Updating Group A with an invalid result.
     */
    public function testUpdatePersonAuthorisationForTestingMotForGroupAAsAInvalidResult()
    {
        $this->setExpectedException(BadRequestException::class, 'Validation errors encountered');

        // When: a person has all classes as for Group A (1 and 2)
        $vehicleClasses = [];
        for ($i = 1; $i <= 7; ++$i) {
            if ($i == 6) {
                continue;
            }
            $vehicleClasses[] = (new VehicleClass($i, $i))->setId($i)->setCode($i);
        }

        $this->setPersonAuthorisationForTesting($vehicleClasses);

        $response = $this->getService()->getPersonalTestingAuthorisation(self::PERSON_ID);
        $authForTesting = $response->toArray();

        $this->assertEquals(
            AuthorisationForTestingMotStatusCode::QUALIFIED,
            $authForTesting['class1']
        );
        $this->assertEquals(
            AuthorisationForTestingMotStatusCode::QUALIFIED,
            $authForTesting['class2']
        );

        $response = null;

        // And: I request to update status of Group A for this user to INITIAL_TRAINING_NEEDED
        $data['group'] = 1;
        $data['result'] = 'WHAT-IS-THIS-HACKERY';

        // Then: I should receive a validation error
        $this->getService()->updatePersonalTestingAuthorisationGroup(self::PERSON_ID, $data);
    }

    /**
     * Negative Scenario - Updating Group A with an invalid result.
     */
    public function testUpdatePersonAuthorisationForTestingMotForGroupBAsAInvalidResult()
    {
        $this->setExpectedException(BadRequestException::class, 'Validation errors encountered');

        // When: a person has all classes as for Group B (3-5, 7)
        $vehicleClasses = [];
        for ($i = 1; $i <= 7; ++$i) {
            if ($i == 6) {
                continue;
            }
            $vehicleClasses[] = (new VehicleClass($i, $i))->setId($i)->setCode($i);
        }

        $this->setPersonAuthorisationForTesting($vehicleClasses);

        $response = $this->getService()->getPersonalTestingAuthorisation(self::PERSON_ID);
        $authForTesting = $response->toArray();

        $this->assertEquals(
            AuthorisationForTestingMotStatusCode::QUALIFIED,
            $authForTesting['class3']
        );
        $this->assertEquals(
            AuthorisationForTestingMotStatusCode::QUALIFIED,
            $authForTesting['class4']
        );
        $this->assertEquals(
            AuthorisationForTestingMotStatusCode::QUALIFIED,
            $authForTesting['class5']
        );
        $this->assertEquals(
            AuthorisationForTestingMotStatusCode::QUALIFIED,
            $authForTesting['class7']
        );

        $response = null;

        // And: I request to update status of Group B for this user to INITIAL_TRAINING_NEEDED
        $data['group'] = 2;
        $data['result'] = 'WHAT-IS-THIS-HACKERY';

        // Then: I should receive a validation error
        $this->getService()->updatePersonalTestingAuthorisationGroup(self::PERSON_ID, $data);
    }

    public function testUserHasNoAuthorisationForMotTestingRecordsOnUpdateGroupA()
    {
        // When: I want to update an user that has no Auth For Mot Testing
        $response = $this->getService()->getPersonalTestingAuthorisation(self::PERSON_ID);
        $authForTesting = $response->toArray();

        $this->assertNull(
            $authForTesting['class1']
        );
        $this->assertNull(
            $authForTesting['class2']
        );

        $response = null;

        // And: I want to set a status for Group A to INITIAL TRAINING NEEDED
        $data['group'] = 1;
        $data['result'] = AuthorisationForTestingMotStatusCode::INITIAL_TRAINING_NEEDED;

        $response = $this->getService()->updatePersonalTestingAuthorisationGroup(self::PERSON_ID, $data);

        // Then: Group A should be set to INITIAL TRAINING NEEDED
        $this->assertNotNull($response);

        $authForTesting = $response->toArray();

        $this->assertEquals(
            AuthorisationForTestingMotStatusCode::INITIAL_TRAINING_NEEDED,
            $authForTesting['class1']
        );
        $this->assertEquals(
            AuthorisationForTestingMotStatusCode::INITIAL_TRAINING_NEEDED,
            $authForTesting['class2']
        );

        // Then: Group B should be set to null
        $this->assertNull(
            $authForTesting['class3']
        );
        $this->assertNull(
            $authForTesting['class4']
        );
        $this->assertNull(
            $authForTesting['class5']
        );
        $this->assertNull(
            $authForTesting['class7']
        );
    }

    public function testUserHasNoAuthorisationForMotTestingRecordsOnUpdateGroupB()
    {
        // When: I want to update an user that has no Auth For Mot Testing
        $response = $this->getService()->getPersonalTestingAuthorisation(self::PERSON_ID);
        $authForTesting = $response->toArray();

        $this->assertNull(
            $authForTesting['class1']
        );
        $this->assertNull(
            $authForTesting['class2']
        );

        $response = null;

        // And: I want to set a status for Group B to INITIAL TRAINING NEEDED
        $data['group'] = 2;
        $data['result'] = AuthorisationForTestingMotStatusCode::INITIAL_TRAINING_NEEDED;

        $response = $this->getService()->updatePersonalTestingAuthorisationGroup(self::PERSON_ID, $data);

        // Then: Group A should be set to null
        $this->assertNotNull($response);

        $authForTesting = $response->toArray();

        $this->assertNull(
            $authForTesting['class1']
        );
        $this->assertNull(
            $authForTesting['class2']
        );

        // Then: Group B should be set to INITIAL_TRAINING_NEEDED
        $this->assertEquals(
            AuthorisationForTestingMotStatusCode::INITIAL_TRAINING_NEEDED,
            $authForTesting['class3']
        );
        $this->assertEquals(
            AuthorisationForTestingMotStatusCode::INITIAL_TRAINING_NEEDED,
            $authForTesting['class4']
        );
        $this->assertEquals(
            AuthorisationForTestingMotStatusCode::INITIAL_TRAINING_NEEDED,
            $authForTesting['class5']
        );
        $this->assertEquals(
            AuthorisationForTestingMotStatusCode::INITIAL_TRAINING_NEEDED,
            $authForTesting['class7']
        );
    }

    private function setPersonAuthorisationForTesting($vehicleClasses)
    {
        $id = 1;
        foreach ($vehicleClasses as $vehicleClass) {
            $this->person->addAuthorisationForTestingMot(
                (new AuthorisationForTestingMot())->setId($id)
                                                  ->setVehicleClass(
                                                      $vehicleClass
                                                  )
                                                  ->setStatus(
                                                      (new AuthorisationForTestingMotStatus())->setId($id)
                                                                                              ->setName('Qualified')
                                                                                              ->setCode('QLFD')
                                                  )
            );

            ++$id;
        }
    }

    private function getVehicleClassRepositoryWithMocks()
    {
        $returnValueMap = [];

        foreach (VehicleClassCode::getAll() as $statusCode) {
            $returnValueMap[] = [
                $statusCode, (new VehicleClass($statusCode, $statusCode)),
            ];
        }

        $mock = XMock::of(VehicleClassRepository::class);
        $mock->expects($this->any())
             ->method('getByCode')
             ->will(
                 $this->returnValueMap($returnValueMap)
             );

        return $mock;
    }

    /**
     * @return PersonalAuthorisationForMotTestingService
     */
    private function getService()
    {
        return new PersonalAuthorisationForMotTestingService(
            $this->entityManager,
            $this->notificationService,
            $this->personalAuthMotTestValidator,
            $this->authorisationService,
            $this->eventService,
            $this->personService,
            $this->authorisationForTestingMotStatusRepository,
            $this->vehicleClassRepository,
            $this->authenticationService
        );
    }
}
