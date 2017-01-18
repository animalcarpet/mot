<?php

namespace DvsaMotApiTest\Service;

use Doctrine\ORM\EntityManager;
use DvsaAuthentication\Identity;
use DvsaCommon\Enum\MotTestTypeCode;
use DvsaCommon\Enum\VehicleClassCode;
use DvsaCommonApi\Authorisation\Assertion\ReadMotTestAssertion;
use DvsaCommonApiTest\Service\AbstractServiceTestCase;
use DvsaCommonApiTest\Transaction\TestTransactionExecutor;
use DvsaCommonTest\Bootstrap;
use DvsaCommonTest\Date\TestDateTimeHolder;
use DvsaCommonTest\TestUtils\XMock;
use DvsaEntities\Entity\Colour;
use DvsaEntities\Entity\ContactDetail;
use DvsaEntities\Entity\CountryOfRegistration;
use DvsaEntities\Entity\FuelType;
use DvsaEntities\Entity\Make;
use DvsaEntities\Entity\Model;
use DvsaEntities\Entity\ModelDetail;
use DvsaEntities\Entity\MotTest;
use DvsaEntities\Entity\MotTestType;
use DvsaEntities\Entity\Person;
use DvsaEntities\Entity\Site;
use DvsaEntities\Entity\SiteContactType;
use DvsaEntities\Entity\Vehicle;
use DvsaEntities\Entity\VehicleClass;
use DvsaEntities\Repository\ConfigurationRepository;
use DvsaEntities\Repository\MotTestRepository;
use DvsaEntities\Repository\MotTestTypeRepository;
use DvsaEntities\Repository\PersonRepository;
use DvsaMotApi\Helper\MysteryShopperHelper;
use DvsaMotApi\Service\CertificateCreationService;
use DvsaMotApi\Service\CreateMotTestService;
use DvsaMotApi\Service\Mapper\MotTestMapper;
use DvsaMotApi\Service\MotTestService;
use DvsaAuthentication\Service\OtpService;
use DvsaMotApi\Service\TesterService;
use DvsaMotApi\Service\Validator\MotTestValidator;
use DvsaMotApi\Service\Validator\RetestEligibility\RetestEligibilityValidator;
use OrganisationApi\Service\OrganisationService;
use PHPUnit_Framework_MockObject_MockObject as MockObj;
use VehicleApi\Service\VehicleService;
use Zend\Authentication\AuthenticationService;
use Dvsa\Mot\ApiClient\Service\VehicleService as NewVehicleService;
/**
 * Base for unit tests for classes inherited from AbstractMotTestService
 */
abstract class AbstractMotTestServiceTest extends AbstractServiceTestCase
{
    const VALID_USER = 'valid-user';

    const MOCK_MOTTEST_DATE_HELPER = 'mockMotTestDateHelper';
    const MOCK_DATETIME_HOLDER = 'mockDateTimeHolder';

    /** @var EntityManager|MockObj  */
    protected $mockEntityManager;

    /** @var MotTestValidator|MockObj  */
    protected $mockMotTestValidator;

    /** @var  TesterService|MockObj */
    protected $mockTesterService;

    /** @var  \DvsaAuthorisation\Service\AuthorisationServiceInterface|MockObj */
    protected $mockAuthService;

    /** @var  MotTestRepository|MockObj */
    protected $mockMotTestRepository;

    /** @var  PersonRepository|MockObj */
    protected $mockPersonRepository;

    /** @var  MotTestMapper|MockObj */
    protected $mockMotTestMapper;

    /** @var  RetestEligibilityValidator|MockObj */
    protected $mockRetestEligibilityValidator;

    /** @var  ConfigurationRepository|MockObj */
    protected $mockConfigurationRepository;

    /** @var  ReadMotTestAssertion|MockObj */
    protected $mockReadMotTestAssertion;

    /** @var  OtpService|MockObj */
    protected $mockOtpService;

    /** @var  OrganisationService|MockObj */
    protected $mockOrganisationService;

    /** @var  VehicleService|MockObj */
    protected $mockVehicleService;

    /** @var  CertificateCreationService|MockObj */
    protected $mockCertificateCreationService;

    /** @var  MotTestTypeRepository|MockObj */
    protected $mockMotTestTypeRepository;

    /** @var  TestDateTimeHolder */
    protected $dateTimeHolder;

    /** @var CreateMotTestService  */
    protected $mockCreateMotTestService;

    /** @var NewVehicleService */
    protected $mockNewVehicleService;

    /** @var  MysteryShopperHelper|MockObj */
    protected $mockMysteryShopperHelper;

    public function setUp()
    {
        unset(
            $this->mockEntityManager,
            $this->mockAuthService,
            $this->mockMotTestValidator,
            $this->mockTesterService,
            $this->mockMotTestRepository,
            $this->mockMotTestMapper,
            $this->mockRetestEligibilityValidator,
            $this->mockConfigurationRepository,
            $this->mockReadMotTestAssertion,
            $this->mockOtpService,
            $this->mockOrganisationService,
            $this->mockVehicleService,
            $this->mockMotTestTypeRepository,
            $this->mockCreateMotTestService,
            $this->mockNewVehicleService,
            $this->mockMysteryShopperHelper
        );

        parent::setUp();
    }

    public static function getTestMotTestEntity()
    {
        $make = new Make();
        $model = new Model();
        $model->setMake($make);
        $modelDetail = new ModelDetail();
        $modelDetail->setModel($model)
            ->setVehicleClass(new VehicleClass(VehicleClassCode::CLASS_4))
            ->getFuelType(new FuelType());
        $vehicle = new Vehicle();
        $vehicle->setVersion(1);
        $vehicle->setModelDetail($modelDetail);
        $vehicle->setColour(new Colour());
        $vehicle->setCountryOfRegistration((new CountryOfRegistration()));
        $type = (new MotTestType())->setCode(MotTestTypeCode::NORMAL_TEST);
        $motTest = new MotTest();
        $motTest
            ->setVehicle($vehicle)
            ->setVehicleVersion($vehicle->getVersion())
            ->setMotTestType($type);
        $motTest->setTester(self::getTestTester());

        $motTest->setVehicleTestingStation(
            (new Site())->setContact((new ContactDetail()), (new SiteContactType()))
        );

        return $motTest;
    }

    protected static function getTestTester()
    {
        $tester = new Person();
        $tester->setId(1);
        $tester->setUsername('tester1');

        return $tester;
    }

    /**
     * @param \DateTime $currentDateTime
     *
     * @return array
     * @throws \Exception
     */
    public function getMocksForMotTestService(\DateTime $currentDateTime = null, $isAuthorised = true)
    {
        $this->mockMotTestRepository = $this->getMockRepository(MotTestRepository::class);
        $this->mockTesterService = $this->getMockWithDisabledConstructor(TesterService::class);
        $this->mockConfigurationRepository = $this->getMockWithDisabledConstructor(ConfigurationRepository::class);
        $this->mockRetestEligibilityValidator = $this->getMockWithDisabledConstructor(
            RetestEligibilityValidator::class
        );
        $this->mockOtpService = $this->getMockWithDisabledConstructor(OtpService::class);
        $this->mockOrganisationService = $this->getMockWithDisabledConstructor(OrganisationService::class);
        $this->mockVehicleService = $this->getMockWithDisabledConstructor(VehicleService::class);
        $this->mockCertificateCreationService = $this->getMockWithDisabledConstructor(
            CertificateCreationService::class
        );
        //  --  mock DateTimeHolder --
        if ($currentDateTime === null) {
            $currentDateTime = new \DateTime();
        }

        $this->dateTimeHolder = new TestDateTimeHolder($currentDateTime);

        $this->mockMotTestTypeRepository = $this->getMockWithDisabledConstructor(MotTestTypeRepository::class);
        $this->mockMotTestTypeRepository->expects($this->any())
            ->method('findOneByCode')
            ->with([MotTestTypeCode::NORMAL_TEST])
            ->willReturn((new MotTestType())->setCode(MotTestTypeCode::NORMAL_TEST));

        $this->mockMotTestTypeRepository->expects($this->any())
            ->method('findOneByCode')
            ->with([MotTestTypeCode::RE_TEST])
            ->willReturn((new MotTestType())->setCode(MotTestTypeCode::RE_TEST));

        $motIdentity = new Identity(new Person());
        $this->mockEntityManager = $this->getMockEntityManager();

        $this->mockMotTestValidator = $this->getMockTestValidator();
        $this->mockAuthService = $this->getMockAuthorizationService($isAuthorised);
        $this->mockMotTestMapper = $this->getMockMotTestMapper();

        $this->mockReadMotTestAssertion = XMock::of(ReadMotTestAssertion::Class);
        $this->mockCreateMotTestService = XMock::of(CreateMotTestService::class);

        $this->mockNewVehicleService = XMock::of(NewVehicleService::class);
        $this->mockMysteryShopperHelper = XMock::of(MysteryShopperHelper::class);

        return [
            'mockMotTestRepository' => $this->mockMotTestRepository,
            'mockMotTestValidator' => $this->mockMotTestValidator,
            'mockTesterService' => $this->mockTesterService,
            'mockEntityManager' => $this->mockEntityManager,
            'mockRetestEligibilityService' => $this->mockRetestEligibilityValidator,
            'mockConfigurationRepository' => $this->mockConfigurationRepository,
            'mockMotTestMapper' => $this->mockMotTestMapper,
            'mockAuthService' => $this->mockAuthService,
            'mockOtpService' => $this->mockOtpService,
            'mockOrganisationService' => $this->mockOrganisationService,
            'mockVehicleService' => $this->mockVehicleService,
            self::MOCK_DATETIME_HOLDER => $this->dateTimeHolder,
            'mockIdentity' => $motIdentity,
            'mockMotTestTypeRepository' => $this->mockMotTestTypeRepository,
            'mockReadMotTestAssertion' => $this->mockReadMotTestAssertion,
            'mockCreateMotTestService' => $this->mockCreateMotTestService,
            'mockNewVehicleService' => $this->mockNewVehicleService,
            'mockMysteryShopperHelper' => $this->mockMysteryShopperHelper
        ];
    }

    public function getServiceLocator()
    {
        return Bootstrap::getServiceManager();
    }

    protected function constructMotTestServiceWithMocks(array $mocks = null)
    {
        $motTestService = new MotTestService(
            $this->mockEntityManager,
            $this->mockMotTestValidator,
            $this->mockAuthService,
            $this->mockConfigurationRepository,
            $this->mockMotTestMapper,
            $this->mockReadMotTestAssertion,
            $this->mockCreateMotTestService,
            $this->mockMotTestRepository,
            $this->mockMysteryShopperHelper
        );

        $this->mockClassField($motTestService, 'dateTimeHolder', $this->dateTimeHolder);

        TestTransactionExecutor::inject($motTestService);

        return $motTestService;
    }

    protected function getMockTestValidator()
    {
        return $this->getMockWithDisabledConstructor(MotTestValidator::class);
    }

    protected function getMockMotTestMapper()
    {
        return $this->getMockWithDisabledConstructor(MotTestMapper::class);
    }
}
