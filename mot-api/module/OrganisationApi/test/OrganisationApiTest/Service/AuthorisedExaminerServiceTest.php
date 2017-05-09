<?php

namespace OrganisationApiTest\Service;

use Doctrine\ORM\EntityManager;
use DvsaAuthorisation\Service\AuthorisationServiceInterface;
use DvsaCommon\Auth\MotIdentityInterface;
use DvsaCommon\Constants\OrganisationType as OrganisationTypeConst;
use DvsaCommon\Date\DateTimeHolder;
use DvsaCommon\Dto\Organisation\OrganisationContactDto;
use DvsaCommon\Dto\Organisation\OrganisationDto;
use DvsaCommon\Dto\Organisation\AuthorisedExaminerAuthorisationDto;
use DvsaCommon\Enum\CompanyTypeCode;
use DvsaCommon\Enum\EventTypeCode;
use DvsaCommon\Enum\OrganisationContactTypeCode;
use DvsaCommon\Exception\UnauthorisedException;
use DvsaCommon\Utility\ArrayUtils;
use DvsaCommonApi\Filter\XssFilter;
use DvsaCommonApi\Service\ContactDetailsService;
use DvsaCommonApiTest\Service\AbstractServiceTestCase;
use DvsaCommonTest\TestUtils\TestCasePermissionTrait;
use DvsaCommonTest\TestUtils\XMock;
use DvsaEntities\Entity\Address;
use DvsaEntities\Entity\AuthForAeStatus;
use DvsaEntities\Entity\AuthorisationForAuthorisedExaminer;
use DvsaEntities\Entity\CompanyType;
use DvsaEntities\Entity\ContactDetail;
use DvsaEntities\Entity\Event;
use DvsaEntities\Entity\EventOrganisationMap;
use DvsaEntities\Entity\Organisation;
use DvsaEntities\Entity\OrganisationContact;
use DvsaEntities\Entity\OrganisationContactType;
use DvsaEntities\Entity\OrganisationType;
use DvsaEntities\Entity\Person;
use DvsaEntities\Entity\Site;
use DvsaEntities\Repository\AuthForAeStatusRepository;
use DvsaEntities\Repository\AuthorisationForAuthorisedExaminerRepository;
use DvsaEntities\Repository\CompanyTypeRepository;
use DvsaEntities\Repository\OrganisationContactTypeRepository;
use DvsaEntities\Repository\OrganisationRepository;
use DvsaEntities\Repository\OrganisationTypeRepository;
use DvsaEntities\Repository\PersonRepository;
use DvsaEntities\Repository\SiteRepository;
use DvsaEventApi\Service\EventService;
use OrganisationApi\Service\AuthorisedExaminerService;
use OrganisationApi\Service\Mapper\OrganisationMapper;
use OrganisationApi\Service\Validator\AuthorisedExaminerValidator;
use PHPUnit_Framework_MockObject_MockObject as MockObj;

/**
 * Class AuthorisedExaminerServiceTest.
 */
class AuthorisedExaminerServiceTest extends AbstractServiceTestCase
{
    use TestCasePermissionTrait;

    const SITE_ID = 1;
    const AE_ID = 8888;
    const AE_REF_NR = 'UT123456';
    const PERSON_ID = 7777;

    /**
     * @var AuthorisationServiceInterface|MockObj
     */
    private $mockAuthService;
    /**
     * @var MotIdentityInterface|MockObj
     */
    private $mockIdentity;
    /** @var AuthorisedExaminerService */
    private $authorisedExaminerService;
    /** @var PersonRepository|MockObj */
    private $mockPersonRepo;
    /** @var SiteRepository|MockObj */
    private $mockSiteRepo;
    /** @var OrganisationRepository|MockObj */
    private $mockOrganisationRepo;
    /** @var OrganisationTypeRepository|MockObj */
    private $mockOrganisationTypeRepo;
    /** @var OrganisationContactTypeRepository|MockObj */
    private $mockOrgContactTypeRepo;
    /** @var CompanyTypeRepository|MockObj */
    private $mockCompanyTypeRepo;
    /** @var ContactDetailsService|MockObj */
    private $contactDetailsService;
    /** @var EntityManager|MockObj */
    private $entityManager;
    /** @var AuthorisationForAuthorisedExaminerRepository|MockObj */
    private $mockAuthForAeRepo;
    /** @var AuthForAeStatusRepository|MockObj */
    private $authForAeStatusRepository;
    /** @var XssFilter|MockObj */
    private $mockXssFilter;
    /**
     * @var EventService|MockObj
     */
    private $mockEventService;

    /** @var Organisation */
    private $authorisedExaminer;
    /** @var AuthorisedExaminerValidator|MockObj */
    private $validator;

    public function setUp()
    {
        $this->entityManager = XMock::of(EntityManager::class);
        $this->mockAuthService = XMock::of(AuthorisationServiceInterface::class);
        $this->mockIdentity = XMock::of(MotIdentityInterface::class);
        $this->contactDetailsService = XMock::of(ContactDetailsService::class);
        $this->mockOrganisationRepo = XMock::of(OrganisationRepository::class);
        $this->mockPersonRepo = XMock::of(PersonRepository::class);
        $this->mockSiteRepo = XMock::of(SiteRepository::class, ['getAllAreaOffices', 'find']);
        $this->mockOrganisationTypeRepo = XMock::of(OrganisationTypeRepository::class, ['findOneByName']);
        $this->mockCompanyTypeRepo = XMock::of(CompanyTypeRepository::class, ['findOneByName', 'getByCode']);
        $this->mockOrgContactTypeRepo = XMock::of(OrganisationContactTypeRepository::class, ['getByCode']);
        $this->authForAeStatusRepository = XMock::of(AuthForAeStatusRepository::class);
        $this->mockXssFilter = XMock::of(XssFilter::class, ['filter']);
        $this->mockAuthForAeRepo = XMock::of(AuthorisationForAuthorisedExaminerRepository::class);
        $this->mockEventService = XMock::of(EventService::class);
        $this->validator = XMock::of(AuthorisedExaminerValidator::class);

        $aoList = $this->fakedAreaOfficeList();
        $this->mockSiteRepo->expects($this->any())
            ->method('getAllAreaOffices')
            ->willReturn($aoList);

        $this->authorisedExaminerService = new AuthorisedExaminerService(
            $this->entityManager,
            $this->mockAuthService,
            $this->mockIdentity,
            $this->contactDetailsService,
            $this->mockEventService,
            $this->mockOrganisationRepo,
            $this->mockPersonRepo,
            $this->mockOrganisationTypeRepo,
            $this->mockCompanyTypeRepo,
            $this->mockOrgContactTypeRepo,
            new OrganisationMapper(
                $this->mockOrganisationTypeRepo,
                $this->mockCompanyTypeRepo
            ),
            $this->authForAeStatusRepository,
            $this->mockXssFilter,
            $this->mockAuthForAeRepo,
            $this->validator,
            new DateTimeHolder(),
            $this->mockSiteRepo
        );

        $this->mockMethod($this->validator, 'validate', $this->any(), true);
        $this->authorisedExaminer = $this->buildAuthorisedExaminer();

        $this->mockMethod($this->mockIdentity, 'getUsername', $this->any(), self::PERSON_ID);

        // Set organisation repository return data
        $this->mockMethod(
            $this->mockOrganisationTypeRepo,
            'findOneByName',
            $this->any(),
            function ($name) {
                return (new OrganisationType())->setName($name);
            }
        );

        $this->mockMethod(
            $this->mockCompanyTypeRepo,
            'getByCode',
            $this->any(),
            function ($code) {
                return (new CompanyType())->setCode($code);
            }
        );

        $this->mockMethod(
            $this->mockCompanyTypeRepo,
            'findOneByName',
            $this->any(),
            function ($name) {
                return (new CompanyType())->setName($name);
            }
        );

        $this->mockMethod(
            $this->mockOrgContactTypeRepo,
            'getByCode',
            $this->any(),
            function ($code) {
                return (new OrganisationContactType())->setCode($code);
            }
        );

        $this->mockMethod(
            $this->mockXssFilter,
            'filter',
            $this->any(),
            function ($dto) {
                return $dto;
            }
        );
    }

    public function testGetAuthorisedExaminer()
    {
        // This test doesn't assert anything. It's to check if code is not broken.
        $this->mockOrganisationRepo->expects($this->any())->method('getAuthorisedExaminer')->will(
            $this->returnValue($this->authorisedExaminer)
        );

        $this->authorisedExaminerService->get(1);
    }

    public function testGetAuthorisedExaminersForPerson()
    {
        // This test doesn't assert anything. It's to check if code is not broken.
        $person = $this->getMockWithDisabledConstructor(Person::class);
        $person->expects($this->any())->method('findAuthorisedExaminers')->will(
            $this->returnValue([$this->authorisedExaminer])
        );

        $this->mockPersonRepo->expects($this->any())->method('get')->will(
            $this->returnValue($person)
        );

        $this->authorisedExaminerService->getAuthorisedExaminersForPerson(1);
    }

    public function buildAuthorisedExaminer()
    {
        $organisation = new Organisation();

        $contactDetails = new ContactDetail();
        $contactDetails->setAddress(new Address());

        $registeredCompanyContactType = new OrganisationContactType();
        $correspondenceType = new OrganisationContactType();

        $registeredContact = new OrganisationContact($contactDetails, $registeredCompanyContactType);
        $correspondenceContact = new OrganisationContact($contactDetails, $correspondenceType);

        $organisation->addContact($registeredContact);
        $organisation->addContact($correspondenceContact);

        $authorisedExaminer = new AuthorisationForAuthorisedExaminer();
        $authorisedExaminer->setOrganisation($organisation);
        $organisation->setAuthorisedExaminer($authorisedExaminer);

        return $organisation;
    }

    public function testCreate()
    {
        //  logical block :: mock
        $contactDto = (new OrganisationContactDto())
            ->setType(OrganisationContactTypeCode::CORRESPONDENCE);

        $contactDetails = new ContactDetail();

        $this->mockMethod($this->mockAuthForAeRepo, 'getNextAeRef', $this->once(), self::AE_REF_NR);
        $this->mockMethod($this->authForAeStatusRepository, 'getByCode', null, new AuthForAeStatus());

        $this->contactDetailsService->expects($this->once())
            ->method('setContactDetailsFromDto')
            ->with($contactDto, $contactDetails)
            ->willReturn($contactDetails);

        //  check creating event
        $this->mockEventService->expects($this->once())
            ->method('addEvent')
            ->withConsecutive([$this->equalTo(EventTypeCode::DVSA_ADMINISTRATOR_CREATE_AE)])
            ->willReturn(new Event());

        //  check persist
        $entities = [Organisation::class, AuthorisationForAuthorisedExaminer::class, EventOrganisationMap::class];
        foreach ($entities as $idx => $entity) {
            $this->mockMethod($this->entityManager, 'persist', $this->at($idx), null, [$this->isInstanceOf($entity)]);
        }

        //  logical block :: call
        $aeAuth = new AuthorisedExaminerAuthorisationDto();
        $aeAuth->setAssignedAreaOffice(self::SITE_ID);

        $orgDto = new OrganisationDto();
        $orgDto
            ->setAuthorisedExaminerAuthorisation($aeAuth)
            ->setName('unit test')
            ->setRegisteredCompanyNumber('utest reg nr')
            ->setTradingAs('unit trading')
            ->setOrganisationType(OrganisationTypeConst::AUTHORISED_EXAMINER)
            ->setContacts([$contactDto])
            ->setCompanyType(CompanyTypeCode::COMPANY);

        $site = new Site();
        $site->setid(self::SITE_ID);

        $this->mockSiteRepo->expects($this->any())
            ->method('find')
            ->with('3000')
            ->willReturn($site);

        $actual = $this->authorisedExaminerService->create($orgDto);

        //  logical block :: check
        $this->assertArrayHasKey('id', $actual);
        $this->assertArrayHasKey('aeRef', $actual);
    }

    public function testHydrateOrganisation()
    {
        $org = $this->authorisedExaminerService->hydrateOrganisation(new Organisation(), self::getData());

        $organisationType = null;
        if ($org->getOrganisationType()) {
            $organisationType = $org->getOrganisationType()->getName();
        }
        $this->assertEquals($org->getName(), ArrayUtils::tryGet(self::getData(), 'organisationName'));
        $this->assertEquals($org->getTradingAs(), ArrayUtils::tryGet(self::getData(), 'tradingAs'));
        $this->assertEquals($organisationType, ArrayUtils::tryGet(self::getData(), 'organisationType'));
        $this->assertEquals(
            $org->getRegisteredCompanyNumber(),
            ArrayUtils::tryGet(self::getData(), 'registeredCompanyNumber')
        );
    }

    public static function getData()
    {
        return [
            'organisationName' => 'Name',
            'tradingAs' => 'my name company',
            'authorisedExaminerReference' => 'AE1234556',
            'companyType' => 'Limited Company',
            'organisationType' => 'Examining Body',
            'registeredCompanyNumber' => '13123123',
            'addressLine1' => 'qqqq',
            'addressLine2' => 'qqqq',
            'addressLine3' => 'Qqqq',
            'town' => 'qqqq',
            'postcode' => 'qqqqq',
            'email' => 'authorisedexaminerservicetest@dvsa.test',
            'emailConfirmation' => 'authorisedexaminerservicetest@dvsa.test',
            'phoneNumber' => '1111111111111',
            'faxNumber' => '2222222222',
            AuthorisedExaminerService::FIELD_CORRESPONDENCE_CONTACT_DETAILS_SAME => true,
        ];
    }

    public static function getDataWithCorrespondenceContactDetails()
    {
        return array_merge(
            self::getData(),
            [
                AuthorisedExaminerService::FIELD_CORRESPONDENCE_CONTACT_DETAILS_SAME => false,
                'correspondenceAddressLine1' => 'Perferendis modi quis aut qui',
                'correspondenceAddressLine2' => 'Dolore aut at illum dolorem illum ipsam mol',
                'correspondenceAddressLine3' => 'Exercitationem et tempora sapiente vitae quid',
                'correspondenceTown' => 'Odit quia tempor corrupti quasi Nam ipsum rem do',
                'correspondencePostcode' => 'Quis fugia',
                'correspondenceEmail' => 'authorisedexaminerservicetest@dvsa.test',
                'correspondenceEmailConfirmation' => 'authorisedexaminerservicetest@dvsa.test',
                'correspondencePhoneNumber' => '+173-78-9018207',
                'correspondenceFaxNumber' => '+978-77-5435043',
                'areaOfficeNumber' => 9,
            ]
        );
    }

    public function testGetByNumber()
    {
        $serviceReturn = (new AuthorisationForAuthorisedExaminer())
            ->setOrganisation(new Organisation());

        $aeRepository = XMock::of(AuthorisationForAuthorisedExaminer::class, ['findOneBy']);
        $this->mockMethod($aeRepository, 'findOneBy', $this->once(), $serviceReturn);

        $this->mockMethod($this->entityManager, 'getRepository', $this->once(), $aeRepository);

        $this->assertInstanceOf(OrganisationDto::class, $this->authorisedExaminerService->getByNumber('A-12345'));
    }

    /**
     * @expectedException \DvsaCommonApi\Service\Exception\NotFoundException
     */
    public function testGetByNumberReturnNull()
    {
        $aeRepository = XMock::of(AuthorisationForAuthorisedExaminer::class, ['findOneBy']);
        $this->mockMethod($aeRepository, 'findOneBy', $this->once(), null);

        $this->mockMethod($this->entityManager, 'getRepository', $this->once(), $aeRepository);

        $this->authorisedExaminerService->getByNumber('INVALID');
    }

    /**
     * @dataProvider dataProviderTestNoAccessNoPerm
     */
    public function testHaveNoAccessNoPerm($method, $params)
    {
        $result = null;

        //  logical block :: mock
        //  revoke all permissions
        $this->mockAssertGranted($this->mockAuthService, []);
        $this->mockIsGranted($this->mockAuthService, []);
        $this->mockAssertGrantedAtOrganisation($this->mockAuthService, [], self::AE_ID);
        $this->mockIsGrantedAtOrganisation($this->mockAuthService, [], self::AE_ID);

        //  set expected exception
        $this->setExpectedException(UnauthorisedException::class, 'You not have permissions');

        //  logical block :: call
        XMock::invokeMethod($this->authorisedExaminerService, $method, $params);
    }

    public function dataProviderTestNoAccessNoPerm()
    {
        return [
            [
                'method' => 'create',
                'params' => [new OrganisationDto()],
            ],
            ['get', [self::AE_ID]],
            ['getByNumber', [self::AE_ID]],
            ['getAuthorisedExaminersForPerson', [self::PERSON_ID]],
        ];
    }

    protected function fakedAreaOfficeList()
    {
        return [
            [
                'id' => '3000',
                'name' => 'Area Office 01',
                'siteNumber' => '01FOO',
                'areaOfficeNumber' => '01',
            ],
            [
                'id' => '3001',
                'name' => 'Area Office 02',
                'siteNumber' => '02BAR',
                'areaOfficeNumber' => '02',
            ],
        ];
    }
}
