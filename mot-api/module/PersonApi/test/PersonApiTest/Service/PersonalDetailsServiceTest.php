<?php

namespace PersonApiTest\Service;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use DvsaAuthorisation\Service\AuthorisationServiceInterface;
use DvsaCommon\Auth\MotIdentityInterface;
use DvsaCommon\Auth\MotIdentityProviderInterface;
use DvsaCommon\Auth\PermissionAtOrganisation;
use DvsaCommon\Auth\PermissionAtSite;
use DvsaCommon\Enum\PhoneContactTypeCode;
use DvsaCommon\Exception\UnauthorisedException;
use DvsaCommonApi\Filter\XssFilter;
use DvsaCommonApiTest\Service\AbstractServiceTestCase;
use DvsaCommonTest\TestUtils\XMock;
use DvsaEntities\Entity\Address;
use DvsaEntities\Entity\ContactDetail;
use DvsaEntities\Entity\Email;
use DvsaEntities\Entity\Gender;
use DvsaEntities\Entity\Organisation;
use DvsaEntities\Entity\Person;
use DvsaEntities\Entity\PersonContact;
use DvsaEntities\Entity\Phone;
use DvsaEntities\Entity\PhoneContactType;
use DvsaEntities\Entity\Site;
use DvsaEntities\Entity\Title;
use PHPUnit_Framework_MockObject_MockObject as MockObj;
use PersonApi\Dto\PersonDetails;
use PersonApi\Service\PersonalDetailsService;
use PersonApi\Service\Validator\PersonalDetailsValidator;
use DvsaAuthorisation\Service\UserRoleService;

/**
 * Unit tests for PersonDetailsService.
 */
class PersonalDetailsServiceTest extends AbstractServiceTestCase
{
    const USER_ID = 9999;

    /**
     * @var EntityManager|MockObj
     */
    private $entityManagerMock;

    /**
     * @var AuthorisationServiceInterface|MockObj
     */
    private $mockAuthSrv;

    /**
     * @var MotIdentityInterface|MockObj
     */
    private $mockIdentity;

    /**
     * @var MotIdentityProviderInterface|MockObj
     */
    private $mockIdentityProvider;

    /**
     * @var PersonalDetailsValidator|MockObj
     */
    private $mockValidator;

    /**
     * @var XssFilter|MockObj
     */
    private $xssFilterMock;

    public function setUp()
    {
        $this->entityManagerMock = $this
            ->getMockBuilder(EntityManager::class)
            ->disableOriginalConstructor()
            ->setMethods(['getRepository', 'find'])
            ->getMock();

        $this->mockAuthSrv = XMock::of(AuthorisationServiceInterface::class, ['isGranted', 'assertGranted']);
        $this->mockIdentity = XMock::of(MotIdentityInterface::class, ['getUserId']);
        $this->mockIdentityProvider = XMock::of(MotIdentityProviderInterface::class);

        $this->mockIdentityProvider
            ->expects($this->any())
            ->method('getIdentity')
            ->willReturn($this->mockIdentity);

        $this->mockValidator = new PersonalDetailsValidator();

        $this->xssFilterMock = $this->createXssFilterMock();
    }

    public function testIsGrantedToView()
    {
        $person = (new Person())
            ->setId(self::USER_ID)
            ->setTitle(new Title())
            ->setGender(new Gender());

        $contactDetail = XMock::of(ContactDetail::class, ['getAddress']);
        $contactDetail
            ->expects($this->any())
            ->method('getAddress')
            ->willReturn(new Address());
        $phone = new Phone();
        $phoneContactType = PhoneContactTypeCode::PERSONAL;
        $email = new Email();
        $personContactType = new \DvsaEntities\Entity\PersonContactType();
        $personContact = new PersonContact($contactDetail, $personContactType, $person);

        $personContactRepository = $this
            ->getMockBuilder(EntityRepository::class)
            ->disableOriginalConstructor()
            ->getMock();
        $personContactRepository
            ->expects($this->any())
            ->method('findOneBy')
            ->will($this->returnValue($personContact));

        $personContactTypeRepository = $this
            ->getMockBuilder(EntityRepository::class)
            ->disableOriginalConstructor()
            ->getMock();
        $personContactTypeRepository
            ->expects($this->any())
            ->method('findOneBy')
            ->will($this->returnValue($personContactType));

        $phoneContactTypeRepository = $this
            ->getMockBuilder(EntityRepository::class)
            ->disableOriginalConstructor()
            ->getMock();
        $phoneContactTypeRepository
            ->expects($this->exactly(2))
            ->method('findOneBy')
            ->will($this->returnValue($phoneContactType));

        $phoneRepository = $this
            ->getMockBuilder(EntityRepository::class)
            ->disableOriginalConstructor()
            ->getMock();
        $phoneRepository
            ->expects($this->exactly(2))
            ->method('findOneBy')
            ->will($this->returnValue($phone));

        $emailRepository = $this
            ->getMockBuilder(EntityRepository::class)
            ->disableOriginalConstructor()
            ->getMock();
        $emailRepository
            ->expects($this->exactly(2))
            ->method('findOneBy')
            ->will($this->returnValue($email));

        $this
            ->entityManagerMock
            ->expects($this->atLeastOnce())
            ->method('getRepository')
            ->will(
                $this->returnValueMap(
                    [
                        [PersonContact::class, $personContactRepository],
                        [PhoneContactType::class, $phoneContactTypeRepository],
                        [Phone::class, $phoneRepository],
                        [Email::class, $emailRepository],
                        [\DvsaEntities\Entity\PersonContactType::class, $personContactTypeRepository],
                    ]
                )
            );

        $this->mockIdentity->expects($this->any())
            ->method('getUserId')
            ->willReturn(self::USER_ID);

        $service = $this->createService();
        $service->expects($this->once())
            ->method('findPerson')
            ->willReturn($person);

        $actualResult = $service->get(self::USER_ID);

        $expectResult = new PersonDetails(
            $person,
            $personContact->getDetails(),
            $this->entityManagerMock,
            null
        );

        $this->assertEquals($expectResult, $actualResult);
    }

    /**
     * @param int   $personId
     * @param array $sites
     * @param array $orgs
     * @param bool  $expectIsAccess
     *
     * @dataProvider dataProviderTestIsGrantedToView
     *
     * @throws \Exception
     */
    public function testAssertGet($personId, array $sites, array $orgs, $expectIsAccess)
    {
        $this->mockIdentity->expects($this->any())
            ->method('getUserId')
            ->willReturn(self::USER_ID);

        /** @var Person|MockObj $person */
        $person = XMock::of(Person::class, ['findSites', 'findOrganisations']);
        $person->setId($personId);

        //  --  sites   --
        $findItems = [];
        if (!empty($sites)) {
            $granted = [];
            foreach ($sites as $item) {
                $itemId = $item['id'];

                $findItems[] = (new Site())->setId($itemId);

                $granted[] = [PermissionAtSite::VTS_EMPLOYEE_PROFILE_READ, $itemId, $item['isGranted']];
            }

            $this->mockAuthSrv->expects($this->any())
                ->method('isGrantedAtSite')
                ->willReturnMap($granted);
        }

        $person->expects($this->any())
            ->method('findSites')
            ->willReturn($findItems);

        //  --  organisations   --
        $findItems = [];
        if (!empty($orgs)) {
            $granted = [];
            foreach ($orgs as $item) {
                $itemId = $item['id'];

                $findItems[] = (new Organisation())->setId($itemId);

                $granted[] = [PermissionAtOrganisation::AE_EMPLOYEE_PROFILE_READ, $itemId, $item['isGranted']];
            }

            $this->mockAuthSrv->expects($this->any())
                ->method('isGrantedAtOrganisation')
                ->willReturnMap($granted);
        }

        $person->expects($this->any())
            ->method('findOrganisations')
            ->willReturn($findItems);

        //  --  call & check    --
        if (!$expectIsAccess) {
            $this->setExpectedException(UnauthorisedException::class, 'Cannot access profiles of other users');
        }

        // TODO we shouldn't call private methods in tests
        XMock::invokeMethod($this->createService(), 'assertViewGranted', [$person]);
    }

    public function dataProviderTestIsGrantedToView()
    {
        return [
            [
                'personId' => self::USER_ID,
                'sites' => [],
                'orgs' => [],
                'expectIsAccess' => true,
            ],
            [
                'personId' => 101,
                'sites' => [
                    ['id' => 201, 'isGranted' => false],
                    ['id' => 202, 'isGranted' => true],
                    ['id' => 203, 'isGranted' => false],
                ],
                'orgs' => [],
                'expectIsAccess' => true,
            ],
            [
                'personId' => 102,
                'sites' => [],
                'orgs' => [
                    ['id' => 301, 'isGranted' => false],
                    ['id' => 302, 'isGranted' => true],
                    ['id' => 303, 'isGranted' => false],
                ],
                'expectIsAccess' => true,
            ],
            [
                'personId' => 103,
                'sites' => [
                    ['id' => 201, 'isGranted' => false],
                ],
                'orgs' => [
                    ['id' => 301, 'isGranted' => true],
                ],
                'expectIsAccess' => true,
            ],
            [
                'personId' => 103,
                'sites' => [
                    ['id' => 201, 'isGranted' => false],
                ],
                'orgs' => [
                    ['id' => 301, 'isGranted' => false],
                ],
                'expectIsAccess' => false,
            ],
            [
                'personId' => 103,
                'sites' => [],
                'orgs' => [],
                'expectIsAccess' => false,
            ],
        ];
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
     * @return MockObj|PersonalDetailsService
     */
    private function createService()
    {
        /** @var PersonalDetailsService|MockObj $mock */
        $mock = $this->getMockBuilder(PersonalDetailsService::class)
            ->setConstructorArgs(
                [
                    $this->entityManagerMock,
                    $this->mockValidator,
                    $this->mockAuthSrv,
                    $this->mockIdentityProvider,
                    $this->xssFilterMock,
                    XMock::of(UserRoleService::class),
                ]
            )
            ->setMethods(['getUserRoles', 'findPerson', 'findOneByOrThrowException'])
            ->getMock();

        return $mock;
    }
}
