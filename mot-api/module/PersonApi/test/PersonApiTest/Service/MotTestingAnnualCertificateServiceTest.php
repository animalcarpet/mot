<?php
namespace PersonApiTest\Service;

use DvsaCommon\Exception\UnauthorisedException;
use DvsaCommon\ApiClient\Person\MotTestingAnnualCertificate\Dto\MotTestingAnnualCertificateDto;
use DvsaCommon\Enum\VehicleClassGroupCode;
use DvsaCommonApi\Service\Exception\BadRequestException;
use DvsaCommonApiTest\Service\AbstractServiceTestCase;
use DvsaCommonTest\TestUtils\MethodSpy;
use DvsaCommonTest\TestUtils\XMock;
use DvsaEntities\Entity\Person;
use DvsaEntities\Entity\QualificationAnnualCertificate;
use DvsaEntities\Entity\VehicleClassGroup;
use DvsaEntities\Repository\PersonRepository;
use DvsaEntities\Repository\QualificationAnnualCertificateRepository;
use DvsaEntities\Repository\VehicleClassGroupRepository;
use PersonApi\Assertion\MotTestingAnnualCertificateAssertion;
use PersonApi\Service\Mapper\MotTestingAnnualCertificateMapper;
use PersonApi\Service\MotTestingAnnualCertificate\MotTestingAnnualCertificateEventService;
use PersonApi\Service\MotTestingAnnualCertificateService;
use PersonApi\Service\Validator\MotTestingAnnualCertificateValidator;

class MotTestingAnnualCertificateServiceTest extends AbstractServiceTestCase
{
    const PERSON_ID = 1;
    const GROUP = VehicleClassGroupCode::BIKES;
    const CERTIFICATE_NUMBER = "CERTIFICATE NUMBER";
    const SCORE = 10;
    const TEST_ASSERT_EXCEPTION = "test assert exception";
    /** @var  PersonRepository|\PHPUnit_Framework_MockObject_MockObject */
    private $personRepository;
    /** @var  VehicleClassGroupRepository|\PHPUnit_Framework_MockObject_MockObject */
    private $vehicleClassGroupRepository;
    /** @var QualificationAnnualCertificateRepository|\PHPUnit_Framework_MockObject_MockObject */
    private $certificateRepository;
    /** @var  MotTestingAnnualCertificateAssertion|\PHPUnit_Framework_MockObject_MockObject */
    private $assertion;
    /** @var  MotTestingAnnualCertificateEventService|\PHPUnit_Framework_MockObject_MockObject */
    private $event;
    /** @var  MotTestingAnnualCertificateValidator|\PHPUnit_Framework_MockObject_MockObject */
    private $validator;
    /** @var  MotTestingAnnualCertificateMapper|\PHPUnit_Framework_MockObject_MockObject */
    private $mapper;
    /** @var  MotTestingAnnualCertificateService|\PHPUnit_Framework_MockObject_MockObject */
    private $sut;
    /** @var  \DateTime */
    private $examDate;

    public function setUp()
    {
        $this->personRepository = XMock::of(PersonRepository::class);
        $this->personRepository->expects($this->any())
            ->method("get")
            ->willReturn($this->createPerson());

        $this->vehicleClassGroupRepository = XMock::of(VehicleClassGroupRepository::class);
        $this->vehicleClassGroupRepository->expects($this->any())
            ->method("getByCode")
            ->willReturn($this->createVehicleClassGroup());

        $this->certificateRepository = XMock::of(QualificationAnnualCertificateRepository::class);

        $this->assertion = XMock::of(MotTestingAnnualCertificateAssertion::class);
        $this->assertion->expects($this->any())
            ->method("assertGrantedView")
            ->willReturn(null);
        $this->assertion->expects($this->any())
            ->method("assertGrantedCreate")
            ->willReturn(null);

        $this->event = XMock::of(MotTestingAnnualCertificateEventService::class);
        $this->validator = new MotTestingAnnualCertificateValidator();

        $this->mapper = new MotTestingAnnualCertificateMapper();

        $this->sut = new MotTestingAnnualCertificateService(
            $this->personRepository,
            $this->vehicleClassGroupRepository,
            $this->certificateRepository,
            $this->assertion,
            $this->event
        );

        $this->examDate = new \DateTime();
    }

    public function testCreateCertificateIsSavedAndEventIsSent()
    {
        $repositorySpy = new MethodSpy($this->certificateRepository, "save");
        $eventSpy = new MethodSpy($this->event, "sendCreateEvent");

        /** @var MotTestingAnnualCertificateDto $dto */
        $dto = $this->sut->create(
            self::PERSON_ID,
            self::GROUP,
            $this->createMotTestingAnnualCertificateDto()
        );

        /** @var QualificationAnnualCertificate $entity */
        $entity = $repositorySpy->getInvocations()[0]->parameters[0];
        $this->assertEquals($dto->getCertificateNumber(), $entity->getCertificateNumber());
        $this->assertEquals($dto->getScore(), $entity->getScore());
        $this->assertEquals($dto->getExamDate(), $entity->getDateAwarded());

        $eventEntity = $eventSpy->getInvocations()[0]->parameters[0];
        $this->assertEquals($entity, $eventEntity);
    }


    public function testCreateExceptionIsThrownWhenInputIsInvalid()
    {
        $this->setExpectedException(BadRequestException::class);
        $input = new MotTestingAnnualCertificateDto();
        $input->setExamDate(new \DateTime())
            ->setScore(101);

        $this->sut->create(self::PERSON_ID, self::GROUP, $input);
    }

    public function testUpdateReturnsDtoOfUpdatedAnnualCertificate()
    {
        $this
            ->assertion
            ->expects($this->any())
            ->method("assertGrantedUpdate")
            ->willReturn(null);

        $vehicleClassGroup = $this->createVehicleClassGroup();
        $person = $this->createPerson();
        $person->setUsername("John Tester");

        $certificate = $this->createCertificate($vehicleClassGroup, $person);

        $oldCertificate = clone $certificate;

        $this
            ->certificateRepository
            ->expects($this->any())
            ->method("getOneByIdAndGroupAndPersonId")
            ->willReturn($certificate);

        $this
            ->certificateRepository
            ->expects($this->once())
            ->method("save")
            ->with($certificate);

        $this
            ->event
            ->expects($this->once())
            ->method("sendUpdateEvent")
            ->with($oldCertificate, $certificate)
        ;

        $dto = $this->createMotTestingAnnualCertificateDto();
        $actualDto = $this->sut->update(
            $dto->getId(),
            self::PERSON_ID,
            self::GROUP,
            $dto
        );

        $this->assertEquals($dto->getCertificateNumber(), $actualDto->getCertificateNumber());
        $this->assertEquals($dto->getScore(), $actualDto->getScore());
        $this->assertEquals($dto->getExamDate(), $actualDto->getExamDate());
    }

    public function testUpdateExceptionIsThrownWhenInputIsInvalid()
    {
        $this->setExpectedException(BadRequestException::class);
        $input = new MotTestingAnnualCertificateDto();
        $input->setExamDate(new \DateTime())
            ->setScore(101);

        $this->sut->update($input->getId(), self::PERSON_ID, self::GROUP, $input);
    }

    public function testUpdateThrowsExceptionWhenIdDoesNotMatch()
    {
        $this->setExpectedException(BadRequestException::class);
        $dto = new MotTestingAnnualCertificateDto();

        $this->sut->update($dto->getId() + 1, self::PERSON_ID, self::GROUP, $dto);
    }

    public function testGetByGroupReturnsDtos()
    {
        $this->certificateRepository->expects($this->any())
            ->method("findAllByGroupAndPersonId")
            ->willReturn([
                $this->createQualificationAnnualCertificate(1, 2),
                $this->createQualificationAnnualCertificate(3, 4)
            ]);

        /** @var MotTestingAnnualCertificateDto[] $result */
        $result = $this->sut->getListByGroup(self::PERSON_ID, self::GROUP);
        $this->assertEquals(1, $result[0]->getScore());
        $this->assertEquals(2, $result[0]->getCertificateNumber());
        $this->assertEquals(3, $result[1]->getScore());
        $this->assertEquals(4, $result[1]->getCertificateNumber());
    }

    public function testDeleteRemovesFromRepositoryAndSendsEvent()
    {
        $vehicleClassGroup = $this->createVehicleClassGroup();
        $person = $this->createPerson();
        $certificate = $this->createCertificate($vehicleClassGroup, $person);

        $this->certificateRepository
            ->expects($this->once())
            ->method("getOneByIdAndGroupAndPersonId")
            ->willReturn($certificate);

        $this->certificateRepository
            ->expects($this->once())
            ->method("remove")
            ->with($certificate);

        $this->certificateRepository
            ->expects($this->once())
            ->method("flush");

        $this->event->expects($this->once())
            ->method("sendRemoveEvent")
            ->with($certificate);

        $this->sut->delete(self::PERSON_ID, self::GROUP, self::CERTIFICATE_NUMBER);
    }

    public function testDeleteExceptionThrownIfNotGranted()
    {
        $this->assertion
            ->expects($this->once())
            ->method("assertGrantedDelete")
            ->willThrowException(new UnauthorisedException(self::TEST_ASSERT_EXCEPTION));

        $this->setExpectedException(UnauthorisedException::class, self::TEST_ASSERT_EXCEPTION);

        $this->sut->delete(self::PERSON_ID, self::GROUP, self::CERTIFICATE_NUMBER);
    }

    private function createPerson()
    {
        $person = new Person();

        return $person;
    }

    private function createMotTestingAnnualCertificateDto($score = self::SCORE, $certNumber = self::CERTIFICATE_NUMBER)
    {
        $dto = new MotTestingAnnualCertificateDto();
        $dto
            ->setExamDate($this->examDate)
            ->setScore($score)
            ->setCertificateNumber($certNumber);

        return $dto;
    }

    private function createVehicleClassGroup()
    {
        $vehicleClassGroup = new VehicleClassGroup();

        return $vehicleClassGroup;
    }

    private function createQualificationAnnualCertificate($score = self::SCORE, $certNumber = self::CERTIFICATE_NUMBER)
    {
        $certificate = new QualificationAnnualCertificate();
        $certificate
            ->setScore($score)
            ->setCertificateNumber($certNumber)
            ->setDateAwarded(new \DateTime());

        return $certificate;
    }

    /**
     * @param $vehicleClassGroup
     * @param $person
     * @return QualificationAnnualCertificate
     */
    public function createCertificate($vehicleClassGroup, $person)
    {
        $certificate = $this->createQualificationAnnualCertificate(89, "CERT-NUMB-001");

        $certificate
            ->setDateAwarded(new \DateTime("2016-07-09"))
            ->setVehicleClassGroup($vehicleClassGroup)
            ->setPerson($person);

        return $certificate;
    }
}