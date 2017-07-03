<?php

namespace SiteApiTest\Service;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use DvsaCommon\Auth\MotIdentityInterface;
use DvsaCommon\Auth\PermissionAtSite;
use DvsaCommon\Date\DateUtils;
use DvsaCommon\Dto\Site\EnforcementSiteAssessmentDto;
use DvsaCommon\Enum\EventTypeCode;
use DvsaCommon\Exception\UnauthorisedException;
use DvsaCommonApi\Filter\XssFilter;
use DvsaCommonApiTest\Service\AbstractServiceTestCase;
use DvsaCommonTest\TestUtils\MethodSpy;
use DvsaCommonTest\TestUtils\TestCasePermissionTrait;
use DvsaCommonTest\TestUtils\XMock;
use DvsaEntities\Entity\EnforcementSiteAssessment;
use DvsaEntities\Entity\Organisation;
use DvsaEntities\Entity\OrganisationSiteMap;
use DvsaEntities\Entity\Person;
use DvsaEntities\Entity\Site;
use DvsaEntities\Repository\PersonRepository;
use DvsaEventApi\Service\EventService;
use NotificationApi\Service\UserOrganisationNotificationService;
use SiteApi\Service\EnforcementSiteAssessmentService;
use SiteApi\Service\Validator\EnforcementSiteAssessmentValidator;
use DvsaCommonApi\Service\Exception\NotFoundException;
use DvsaAuthorisation\Service\AuthorisationServiceInterface;
use PHPUnit_Framework_MockObject_MockObject as MockObj;

class EnforcementSiteAssessmentServiceTest extends AbstractServiceTestCase
{
    use TestCasePermissionTrait;

    const SITE_ID = 1;
    const AE_ORGANIZATION_ID = 2;
    const TESTER_ID = 3;
    const EXAMINER_ID = 4;
    const AE_REPRESENTATIVE_ID = 5;
    const TESTER_NAME = 'Tester';
    const TESTER_USERNAME = 'tester';
    const EXAMINER_NAME = 'Examiner';
    const EXAMINER_USERNAME = 'examiner';
    const AE_REPRESENTATIVE_NAME = 'AE Representative';
    const AE_REPRESENTATIVE_USERNAME = 'ae_representative';
    const AE_REPRESENTATIVE_ROLE = 'AE Rep';
    const DATE_OF_ASSESSMENT = '2015-01-01';

    /** @var EntityManager|MockObj */
    private $entityManager;
    /** @var EnforcementSiteAssessmentValidator|MockObj */
    private $validator;
    /** @var |MockObj */
    private $config;
    /** @var XssFilter|MockObj */
    private $xssFilter;
    /** @var EventService|MockObj */
    private $eventService;
    /** @var MotIdentityInterface|MockObj */
    private $identity;
    /** @var AuthorisationServiceInterface|MockObj */
    private $authService;
    /** @var EnforcementSiteAssessmentService */
    private $service;
    /** @var UserOrganisationNotificationService|MockObj */
    private $userOrganisationNotificationService;

    public function setUp()
    {
        $this->entityManager = XMock::of(EntityManager::class);
        $this->validator = XMock::of(EnforcementSiteAssessmentValidator::class);
        $this->authService = XMock::of(AuthorisationServiceInterface::class);
        $this->config = [
            'site_assessment' => [
                'green' => ['start' => '0', 'end' => '324.10'],
                'amber' => ['start' => '324.11', 'end' => '459.20'],
                'red' => ['start' => '459.21', 'end' => '999.99'],
            ],
        ];
        $this->eventService = XMock::of(EventService::class);
        $this->xssFilter = $this->createXssFilterMock();
        $this->identity = XMock::of(MotIdentityInterface::class);
        $this->userOrganisationNotificationService = XMock::of(UserOrganisationNotificationService::class);

        $this->service = $this->createService();
    }

    public function testGetRiskAssessmentForUnauthorisedUser()
    {
        $this->mockAssertGrantedAtSite(
            $this->authService,
            [],
            self::SITE_ID
        );

        $this->setExpectedException(UnauthorisedException::class);
        $this->service->getRiskAssessment(self::SITE_ID);
    }

    public function testGetRiskAssessmentForAuthorisedUserWithNoSiteFound()
    {
        $this->mockAssertGrantedAtSite(
            $this->authService,
            [PermissionAtSite::VTS_VIEW_SITE_RISK_ASSESSMENT],
            self::SITE_ID
        );

        $this->mockMethod(
            $this->entityManager,
            'find',
            null,
            null,
            [Site::class, self::SITE_ID]
        );

        $this->service = $this->createService();

        $this->setExpectedException(NotFoundException::class);
        $this->service->getRiskAssessment(self::SITE_ID);
    }

    public function testGetRiskAssessmentForAuthorisedUserWithNoPreviousAssessmentFound()
    {
        $this->mockAssertGrantedAtSite(
            $this->authService,
            [PermissionAtSite::VTS_VIEW_SITE_RISK_ASSESSMENT],
            self::SITE_ID
        );

        $siteMock = XMock::of(Site::class);
        $this->mockMethod(
            $siteMock,
            'getLastSiteAssessment',
            null,
            null,
            null
        );

        $this->mockMethod(
            $this->entityManager,
            'find',
            null,
            $siteMock,
            [Site::class, self::SITE_ID]
        );

        $this->service = $this->createService();

        $this->setExpectedException(NotFoundException::class);
        $this->service->getRiskAssessment(self::SITE_ID);
    }

    public function testGetRiskAssessmentForAuthorisedUser()
    {
        $this->mockAssertGrantedAtSite(
            $this->authService,
            [PermissionAtSite::VTS_VIEW_SITE_RISK_ASSESSMENT],
            self::SITE_ID
        );

        $assessmentEntity = $this->createAssessmentEntity();

        $siteMock = XMock::of(Site::class);
        $this->mockMethod(
            $siteMock,
            'getLastSiteAssessment',
            null,
            $assessmentEntity,
            null
        );

        $this->mockMethod(
            $this->entityManager,
            'find',
            null,
            $siteMock,
            [Site::class, self::SITE_ID]
        );

        $this->service = $this->createService();
        $result = $this->service->getRiskAssessment(self::SITE_ID);
        $expectedResult = $this->createDto();

        $this->assertInstanceOf(EnforcementSiteAssessmentDto::class, $result);
        $this->assertEquals($expectedResult, $result);
    }

    public function testValidateRiskAssessmentForUnauthorisedUser()
    {
        $this->mockAssertGrantedAtSite(
            $this->authService,
            [],
            self::SITE_ID
        );

        $this->setExpectedException(UnauthorisedException::class);
        $this->service->validateRiskAssessment($this->createDto());
    }

    public function testValidateRiskAssessmentForAuthorisedUserWithCorrectDate()
    {
        $this->mockAssertGrantedAtSite(
            $this->authService,
            [PermissionAtSite::VTS_UPDATE_SITE_RISK_ASSESSMENT],
            self::SITE_ID
        );

        $this->prepareMocksForCorrectFullData();

        $dto = $this->createDto(0, true);

        $this->service = $this->createService();
        $result = $this->service->validateRiskAssessment($dto);

        $this->assertEquals($dto, $result);
    }

    public function testCreateRiskAssessmentForUnauthorisedUser()
    {
        $this->mockAssertGrantedAtSite(
            $this->authService,
            [],
            self::SITE_ID
        );

        $this->setExpectedException(UnauthorisedException::class);
        $this->service->createRiskAssessment($this->createDto());
    }


    public function testCreateRiskAssessmentSetsNewLastAssessmentWhenSiteLastAssessmentDoesNotExist()
    {
        $site = new Site();
        $site->setPositions(new ArrayCollection());
        $site->setOrganisation(new Organisation());

        $this->mockMethod(
            $this->entityManager,
            'find',
            null,
            $site,
            [Site::class, self::SITE_ID]
        );

        $this->prepareMocksForCorrectFullData();
        $entityManagerSpy = new MethodSpy($this->entityManager,"persist");

        $dto = $this->createDto(0, true);
        $this->service = $this->createService();
        $this->service->createRiskAssessment($dto);

        $siteAssessment = $this->extractEnforcementSiteAssessmentFromEntityManagerSpy($entityManagerSpy);
        $site = $this->extractSiteFromEntityManagerSpy($entityManagerSpy);

        $this->assertSame($siteAssessment, $site->getLastSiteAssessment());
    }

    public function testCreateRiskAssessmentDoesNotSetOutdatedAssessmentAsLast()
    {
        //there is a site with last site assessment created today
        $date = new \DateTime();
        $organisationSiteMap = new OrganisationSiteMap();
        $organisationSiteMap->setStartDate($date);

        $siteAssessment = $this->createAssessmentEntity(56, $date->format("Y-m-d"));
        $site = new Site();
        $site->setPositions(new ArrayCollection());
        $site->setOrganisation(new Organisation());
        $site->getAssociationWithAe()->add($organisationSiteMap);
        $site->setLastSiteAssessment($siteAssessment);

        $this->mockMethod(
            $this->entityManager,
            'find',
            null,
            $site,
            [Site::class, self::SITE_ID]
        );

        $this->prepareMocksForCorrectFullData();
        $entityManagerSpy = new MethodSpy($this->entityManager,"persist");

        //when I try add new assessment with visit date before current visit date
        $dto = $this->createDto(0, true);
        $this->service = $this->createService();
        $this->service->createRiskAssessment($dto);

        $site = $this->extractSiteFromEntityManagerSpy($entityManagerSpy);

        //then the last assessment is not changed
        $this->assertSame($siteAssessment, $site->getLastSiteAssessment());
    }

    public function testCreateRiskAssessmentForAuthorisedUserWithCorrectData()
    {
        $this->mockAssertGrantedAtSite(
            $this->authService,
            [PermissionAtSite::VTS_UPDATE_SITE_RISK_ASSESSMENT],
            self::SITE_ID
        );

        $this->prepareMocksForCorrectFullData();
        $dto = $this->createDto(0, true);

        $this->eventService
            ->expects($this->atLeastOnce())
            ->method('addEvent')
            ->with(
                EventTypeCode::UPDATE_SITE_ASSESSMENT_RISK_SCORE
            );

        $this->entityManager
            ->expects($this->atLeast(3))
            ->method('persist')
        ;

        $this->entityManager
            ->expects($this->atLeastOnce())
            ->method('flush')
        ;

        $this->service = $this->createService();
        $this->service->createRiskAssessment($dto);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function createXssFilterMock()
    {
        $xssFilterMock = $this
            ->getMockBuilder(XssFilter::class)
            ->disableOriginalConstructor()
            ->getMock();

        $xssFilterMock
            ->method('filter')
            ->will($this->returnArgument(0));

        $xssFilterMock
            ->method('filterMultiple')
            ->will($this->returnArgument(0));

        return $xssFilterMock;
    }

    /**
     * @param MethodSpy $entityManagerSpy
     * @return EnforcementSiteAssessment
     */
    private function extractEnforcementSiteAssessmentFromEntityManagerSpy(MethodSpy $entityManagerSpy)
    {
        $entityManagerInvocations = $entityManagerSpy->getInvocations();
        foreach ($entityManagerInvocations as $invocationObject) {
            foreach ($invocationObject->parameters as $parameter) {
                if ($parameter instanceof EnforcementSiteAssessment) {
                    return $parameter;
                }
            }
        }

        throw new \LogicException(sprintf("Object '%s' not found", EnforcementSiteAssessment::class));
    }

    /**
     * @param MethodSpy $entityManagerSpy
     * @return Site
     */
    private function extractSiteFromEntityManagerSpy(MethodSpy $entityManagerSpy)
    {
        $entityManagerInvocations = $entityManagerSpy->getInvocations();
        foreach ($entityManagerInvocations as $invocationObject) {
            foreach ($invocationObject->parameters as $parameter) {
                if ($parameter instanceof Site) {
                    return $parameter;
                }
            }
        }

        throw new \LogicException(sprintf("Object '%s' not found", Site::class));
    }

    /**
     * @return EnforcementSiteAssessmentDto
     */
    private function createDto($score = 0, $userIsNotAssessor = false)
    {
        $dto = new EnforcementSiteAssessmentDto();
        $dto
            ->setSiteAssessmentScore($score)
            ->setDateOfAssessment(self::DATE_OF_ASSESSMENT)
            ->setSiteId(self::SITE_ID)
            ->setAeOrganisationId(self::AE_ORGANIZATION_ID)
            ->setUserIsNotAssessor($userIsNotAssessor)

            ->setDvsaExaminersFullName(self::EXAMINER_NAME)
            ->setDvsaExaminersUserId(self::EXAMINER_USERNAME)

            ->setAeRepresentativesRole(self::AE_REPRESENTATIVE_ROLE)
            ->setAeRepresentativesFullName(self::AE_REPRESENTATIVE_NAME)
            ->setAeRepresentativesUserId(self::AE_REPRESENTATIVE_USERNAME)

            ->setTesterFullName(self::TESTER_NAME)
            ->setTesterUserId(self::TESTER_USERNAME)
        ;

        return $dto;
    }

    /**
     * @return EnforcementSiteAssessmentService
     */
    private function createService()
    {
        return new EnforcementSiteAssessmentService(
            $this->entityManager,
            $this->validator,
            $this->config,
            $this->identity,
            $this->eventService,
            $this->authService,
            $this->xssFilter,
            $this->userOrganisationNotificationService
        );
    }

    /**
     * @return MockObj|EnforcementSiteAssessment
     *
     * @throws \Exception
     */
    private function createAssessmentEntity($score = 0, $dateOfAssessment= self::DATE_OF_ASSESSMENT)
    {
        $assessment = new EnforcementSiteAssessment();
        $assessment
            ->setSite($this->createSiteEntity())
            ->setAeOrganisationId(self::AE_ORGANIZATION_ID)
            ->setAeRepresentativeName(self::AE_REPRESENTATIVE_NAME)
            ->setAeRepresentativePosition(self::AE_REPRESENTATIVE_ROLE)
            ->setTester(
                $this->createTesterPersonEntity()
            )
            ->setExaminer(
                $this->createExaminerPersonEntity()
            )
            ->setRepresentative(
                $this->createAeRepresentativePersonEntity()
            )
            ->setSiteAssessmentScore($score)
            ->setVisitDate(DateUtils::toDate($dateOfAssessment))
        ;

        return $assessment;
    }

    /**
     * @return Person
     */
    private function createTesterPersonEntity()
    {
        return (new Person())
            ->setId(self::TESTER_ID)
            ->setFirstName(self::TESTER_NAME)
            ->setUsername(self::TESTER_USERNAME)
        ;
    }

    /**
     * @return Person
     */
    private function createAeRepresentativePersonEntity()
    {
        return (new Person())
            ->setId(self::AE_REPRESENTATIVE_ID)
            ->setFirstName(self::AE_REPRESENTATIVE_NAME)
            ->setUsername(self::AE_REPRESENTATIVE_USERNAME);
    }

    /**
     * @return Person
     */
    private function createExaminerPersonEntity()
    {
        return (new Person())
            ->setId(self::EXAMINER_ID)
            ->setFirstName(self::EXAMINER_NAME)
            ->setUsername(self::EXAMINER_USERNAME);
    }

    /**
     * @return Site
     */
    private function createSiteEntity()
    {
        return (new Site())
            ->setId(self::SITE_ID)
            ->setOrganisation(
                (new Organisation())
                    ->setId(self::AE_ORGANIZATION_ID)
            )
        ;
    }

    private function prepareMocksForCorrectFullData()
    {
        $personRepositoryMock = XMock::of(PersonRepository::class);
        $this->mockMethod(
            $personRepositoryMock,
            'findOneBy',
            $this->at(0),
            $this->createTesterPersonEntity(),
            [['username' => self::TESTER_USERNAME]]
        );
        $this->mockMethod(
            $personRepositoryMock,
            'findOneBy',
            $this->at(1),
            $this->createAeRepresentativePersonEntity(),
            [['username' => self::AE_REPRESENTATIVE_USERNAME]]
        );
        $this->mockMethod(
            $personRepositoryMock,
            'findOneBy',
            $this->at(2),
            $this->createExaminerPersonEntity(),
            [['username' => self::EXAMINER_USERNAME]]
        );
        $this->mockMethod(
            $this->entityManager,
            'getRepository',
            null,
            $personRepositoryMock,
            [Person::class]
        );
        $this->mockMethod(
            $this->entityManager,
            'find',
            $this->any(),
            $this->createSiteEntity(),
            [Site::class, self::SITE_ID]
        );
    }
}
