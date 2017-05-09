<?php

namespace OrganisationApiTest\Service;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMException;
use DvsaAuthorisation\Service\AuthorisationServiceInterface;
use DvsaCommon\Auth\MotIdentityInterface;
use DvsaCommon\Date\DateTimeHolder;
use DvsaCommon\Dto\Organisation\OrganisationSiteLinkDto;
use DvsaCommon\Enum\EventTypeCode;
use DvsaCommon\Enum\OrganisationSiteStatusCode;
use DvsaCommon\Exception\UnauthorisedException;
use DvsaCommonApi\Service\Exception\BadRequestException;
use DvsaCommonApi\Service\Exception\NotFoundException;
use DvsaCommonApi\Service\Exception\ServiceException;
use DvsaCommonApiTest\Service\AbstractServiceTestCase;
use DvsaCommonTest\TestUtils\XMock;
use DvsaEntities\Entity\AuthorisationForAuthorisedExaminer;
use DvsaEntities\Entity\Event;
use DvsaEntities\Entity\EventOrganisationMap;
use DvsaEntities\Entity\EventSiteMap;
use DvsaEntities\Entity\Organisation;
use DvsaEntities\Entity\OrganisationSiteMap;
use DvsaEntities\Entity\OrganisationSiteStatus;
use DvsaEntities\Entity\Person;
use DvsaEntities\Entity\Site;
use DvsaEntities\Repository\OrganisationRepository;
use DvsaEntities\Repository\OrganisationSiteMapRepository;
use DvsaEntities\Repository\OrganisationSiteStatusRepository;
use DvsaEntities\Repository\SiteRepository;
use DvsaEventApi\Service\EventService;
use NotificationApi\Dto\Notification as NotificationDto;
use NotificationApi\Service\NotificationService;
use OrganisationApi\Service\Mapper\OrganisationSiteLinkMapper;
use OrganisationApi\Service\SiteLinkService;
use OrganisationApi\Service\Validator\SiteLinkValidator;
use PHPUnit_Framework_MockObject_MockObject as MockObj;
use SiteApi\Service\MotTestInProgressService;

class SiteLinkServiceTest extends AbstractServiceTestCase
{
    const ORG_ID = 9999;
    const ORG_SITE_MAP_ID = null;
    const AE_NUMBER = 1;
    const SITE_ID = 8888;
    const SITE_NUMBER = 'S00001';
    const PERSON_ID = 1;
    const STATUS = 'unitTest_Status';
    const LINK_ID = 1234564;

    /**
     * @var EntityManager|MockObj
     */
    private $mockEntityMngr;
    /**
     * @var AuthorisationServiceInterface|MockObj
     */
    private $mockAuthService;
    /**
     * @var MotIdentityInterface|MockObj
     */
    private $mockIdentity;
    /**
     * @var EventService|MockObj
     */
    private $mockEventService;
    /**
     * @var NotificationService|MockObj
     */
    private $mockNotificationSrv;
    /**
     * @var MotTestInProgressService|MockObj
     */
    private $mockMotTestInProgressSrv;
    /**
     * @var OrganisationRepository|MockObj
     */
    private $mockOrgRepo;
    /**
     * @var SiteRepository|MockObj
     */
    private $mockSiteRepo;
    /**
     * @var OrganisationSiteMapRepository|MockObj
     */
    private $mockOrgSiteMapRepo;
    /**
     * @var OrganisationSiteStatusRepository|MockObj
     */
    private $mockOrgSiteStatusRepo;
    /**
     * @var OrganisationSiteLinkMapper
     */
    private $mockOrgSiteLinkMapper;
    /**
     * @var SiteLinkValidator|MockObj
     */
    private $mockValidator;
    /**
     * @var SiteLinkService
     */
    private $service;

    public function setup()
    {
        $this->mockEntityMngr = $this->getMockEntityManager();
        $this->mockMethod($this->mockEntityMngr, 'getConnection', null, XMock::of(Connection::class));

        $this->mockAuthService = $this->getMockAuthorizationService();
        $this->mockIdentity = XMock::of(MotIdentityInterface::class);

        $this->mockEventService = XMock::of(EventService::class);
        $this->mockNotificationSrv = XMock::of(NotificationService::class);
        $this->mockMotTestInProgressSrv = XMock::of(MotTestInProgressService::class);

        $this->mockOrgRepo = $this->getMockRepository(OrganisationRepository::class);
        $this->mockSiteRepo = $this->getMockRepository(SiteRepository::class);
        $this->mockOrgSiteMapRepo = $this->getMockRepository(OrganisationSiteMapRepository::class);
        $this->mockOrgSiteStatusRepo = $this->getMockRepository(OrganisationSiteStatusRepository::class);

        $this->mockOrgSiteLinkMapper = XMock::of(OrganisationSiteLinkMapper::class);
        $this->mockValidator = XMock::of(SiteLinkValidator::class);

        $this->service = new SiteLinkService(
            $this->mockEntityMngr,
            $this->mockAuthService,
            $this->mockIdentity,
            //  services
            $this->mockEventService,
            $this->mockNotificationSrv,
            $this->mockMotTestInProgressSrv,
            //  repos
            $this->mockOrgRepo,
            $this->mockSiteRepo,
            $this->mockOrgSiteMapRepo,
            $this->mockOrgSiteStatusRepo,
            //  other
            $this->mockOrgSiteLinkMapper,
            $this->mockValidator,
            new DateTimeHolder()
        );
    }

    /**
     * @dataProvider dataProviderTestMethodsPermissionsAndResults
     */
    public function testGetDataMethodsPermissionsAndResults($method, $params, $repoMocks, $permissions, $expect)
    {
        if ($repoMocks !== null) {
            foreach ($repoMocks as $repo) {
                $invocation = isset($repo['call']) ? $repo['call'] : $this->once();
                $result = isset($repo['result']) ? $repo['result'] : null;

                $this->mockMethod(
                    $this->{$repo['class']}, $repo['method'], $invocation, $result, $repo['params']
                );
            }
        }

        //  --  check permission    --
        if ($permissions !== null) {
            $this->assertGrantedAtOrganisation($this->mockAuthService, current($permissions), key($permissions));
        }

        //  --  set expected exception  --
        if (!empty($expect['exception'])) {
            $exception = $expect['exception'];
            $this->setExpectedException($exception['class'], $exception['message'], $exception['code']);
        }

        //  --  call and check result --
        $actual = XMock::invokeMethod($this->service, $method, $params);

        $this->assertSame($expect['result'], $actual);
    }

    public function dataProviderTestMethodsPermissionsAndResults()
    {
        $authForAeEntity = XMock::of(AuthorisationForAuthorisedExaminer::class);
        $this->mockMethod($authForAeEntity, 'getDesignatedManager', $this->any(), new Person());

        $orgEntity = new Organisation();
        $orgEntity
            ->setId(self::ORG_ID)
            ->setAuthorisedExaminer($authForAeEntity);

        $siteEntity = (new Site())
            ->setId(self::SITE_ID);

        $unauthException = [
            'class' => UnauthorisedException::class,
            'message' => 'You not have permissions',
            'code' => 0,
        ];

        $linkEntity = (new OrganisationSiteMap())
            ->setId(self::ORG_SITE_MAP_ID)
            ->setOrganisation($orgEntity)
            ->setSite($siteEntity);

        $orgSiteLinkDto = new OrganisationSiteLinkDto();

        return [
            //  logical block :: getApprovedUnlinkedSite
            //  Success
            [
                'method' => 'getApprovedUnlinkedSite',
                'params' => [],
                'repo' => [
                    [
                        'class' => 'mockSiteRepo',
                        'method' => 'getApprovedUnlinkedSite',
                        'params' => [],
                        'result' => [['id' => self::SITE_ID]],
                    ],
                ],
                'permissions' => null,
                'expect' => [
                    'result' => [['id' => self::SITE_ID]],
                ],
            ],

            //  logical block :: get link data
            //  not found
            [
                'method' => 'get',
                'params' => [
                    'id' => self::LINK_ID,
                ],
                'repo' => [
                    [
                        'class' => 'mockOrgSiteMapRepo',
                        'method' => 'get',
                        'params' => [],
                        'result' => new NotFoundException(OrganisationSiteMapRepository::ERR_ORG_SITE_LINK_NOT_FOUND),
                    ],
                ],
                'permissions' => null,
                'expect' => [
                    'exception' => [
                        'class' => NotFoundException::class,
                        'message' => OrganisationSiteMapRepository::ERR_ORG_SITE_LINK_NOT_FOUND.' not found',
                        'code' => NotFoundException::ERROR_CODE_NOT_FOUND,
                    ],
                ],
            ],
            //  success
            [
                'method' => 'get',
                'params' => [
                    'id' => self::LINK_ID,
                ],
                'repo' => [
                    [
                        'class' => 'mockOrgSiteMapRepo',
                        'method' => 'get',
                        'params' => [],
                        'result' => $linkEntity,
                    ],
                    [
                        'class' => 'mockOrgSiteLinkMapper',
                        'method' => 'toDto',
                        'params' => [],
                        'result' => $orgSiteLinkDto,
                    ],
                ],
                'permissions' => null,
                'expect' => [
                    'result' => $orgSiteLinkDto,
                ],
            ],

            //  logical block :: siteLink
            //  Success
            [
                'method' => 'siteLink',
                'params' => [
                    'orgId' => self::ORG_ID,
                    'siteNumber' => self::SITE_NUMBER,
                ],
                'repo' => [
                    [
                        'class' => 'mockOrgRepo',
                        'method' => 'get',
                        'params' => [self::ORG_ID],
                        'result' => $this->getOrganisation(),
                    ],
                    [
                        'class' => 'mockSiteRepo',
                        'method' => 'getBySiteNumber',
                        'params' => [self::SITE_NUMBER],
                        'result' => $this->getSite(),
                    ],
                    [
                        'class' => 'mockValidator',
                        'method' => 'validateLink',
                        'params' => [$this->getOrganisation(), $this->getSite(), self::ORG_ID, self::SITE_NUMBER],
                        'result' => true,
                    ],
                    [
                        'class' => 'mockOrgSiteStatusRepo',
                        'method' => 'getByCode',
                        'params' => [OrganisationSiteStatusCode::ACTIVE],
                        'result' => new OrganisationSiteStatus(),
                    ],
                ],
                'permissions' => null,
                'expect' => [
                    'result' => ['id' => self::ORG_SITE_MAP_ID],
                ],
            ],

            //  logical block :: siteChangeStatus
            //  can not find link data
            [
                'method' => 'siteChangeStatus',
                'params' => [
                    'linkId' => self::LINK_ID,
                    'statusCode' => self::STATUS,
                ],
                'repo' => [
                    [
                        'class' => 'mockOrgSiteMapRepo',
                        'method' => 'get',
                        'params' => [self::LINK_ID, OrganisationSiteStatusCode::ACTIVE],
                        'result' => new NotFoundException(OrganisationSiteMapRepository::ERR_ORG_SITE_LINK_NOT_FOUND),
                    ],
                ],
                'permissions' => null,
                'expect' => [
                    'exception' => [
                        'class' => NotFoundException::class,
                        'message' => OrganisationSiteMapRepository::ERR_ORG_SITE_LINK_NOT_FOUND.' not found',
                        'code' => NotFoundException::ERROR_CODE_NOT_FOUND,
                    ],
                ],
            ],
            //  not permission
            [
                'method' => 'siteChangeStatus',
                'params' => [
                    'linkId' => self::LINK_ID,
                    'statusCode' => self::STATUS,
                ],
                'repo' => [
                    [
                        'class' => 'mockOrgSiteMapRepo',
                        'method' => 'get',
                        'params' => [self::LINK_ID, OrganisationSiteStatusCode::ACTIVE],
                        'result' => $linkEntity,
                    ],
                ],
                'permissions' => [self::ORG_ID => []],
                'expect' => [
                    'exception' => $unauthException,
                ],
            ],
            //  fail validation
            [
                'method' => 'siteChangeStatus',
                'params' => [
                    'linkId' => self::LINK_ID,
                    'statusCode' => self::STATUS,
                ],
                'repo' => [
                    [
                        'class' => 'mockOrgSiteMapRepo',
                        'method' => 'get',
                        'params' => [self::LINK_ID, OrganisationSiteStatusCode::ACTIVE],
                        'result' => $linkEntity,
                    ],
                    [
                        'class' => 'mockValidator',
                        'method' => 'ValidateUnlink',
                        'params' => [self::STATUS],
                        'result' => new BadRequestException('error', BadRequestException::ERROR_CODE_INVALID_DATA),
                    ],
                ],
                'permissions' => null,
                'expect' => [
                    'exception' => [
                        'class' => BadRequestException::class,
                        'message' => 'error',
                        'code' => BadRequestException::BAD_REQUEST_STATUS_CODE,
                    ],
                ],
            ],
            //  has active mot test
            [
                'method' => 'siteChangeStatus',
                'params' => [
                    'linkId' => self::LINK_ID,
                    'statusCode' => self::STATUS,
                ],
                'repo' => [
                    [
                        'class' => 'mockOrgSiteMapRepo',
                        'method' => 'get',
                        'params' => [self::LINK_ID, OrganisationSiteStatusCode::ACTIVE],
                        'result' => $linkEntity,
                    ],
                    [
                        'class' => 'mockValidator',
                        'method' => 'ValidateUnlink',
                        'params' => [self::STATUS],
                        'result' => null,
                    ],
                    [
                        'class' => 'mockMotTestInProgressSrv',
                        'method' => 'getCountForSite',
                        'params' => [self::SITE_ID],
                        'result' => 1,
                    ],
                ],
                'permissions' => null,
                'expect' => [
                    'exception' => [
                        'class' => ServiceException::class,
                        'message' => SiteLinkService::ERR_UNLINK_SITE_TEST_IN_PROGRESS,
                        'code' => ServiceException::DEFAULT_STATUS_CODE,
                    ],
                ],
            ],
            //  success
            [
                'method' => 'siteChangeStatus',
                'params' => [
                    'linkId' => self::LINK_ID,
                    'statusCode' => self::STATUS,
                ],
                'repo' => [
                    [
                        'class' => 'mockOrgSiteMapRepo',
                        'method' => 'get',
                        'params' => [self::LINK_ID, OrganisationSiteStatusCode::ACTIVE],
                        'result' => $linkEntity,
                    ],
                    [
                        'class' => 'mockValidator',
                        'method' => 'ValidateUnlink',
                        'params' => [self::STATUS],
                        'result' => null,
                    ],
                    [
                        'class' => 'mockMotTestInProgressSrv',
                        'method' => 'getCountForSite',
                        'params' => [self::SITE_ID],
                        'result' => 0,
                    ],
                    [
                        'class' => 'mockOrgSiteStatusRepo',
                        'method' => 'getByCode',
                        'params' => [self::STATUS],
                        'result' => new OrganisationSiteStatus(),
                    ],
                    //  check event
                    [
                        'class' => 'mockEventService',
                        'method' => 'addEvent',
                        'params' => [$this->equalTo(EventTypeCode::UNLINK_AE_SITE)],
                        'result' => new Event(),
                    ],
                    //  check store site event
                    [
                        'class' => 'mockEntityMngr',
                        'method' => 'persist',
                        'call' => $this->at(1),
                        'params' => [$this->isInstanceOf(EventSiteMap::class)],
                    ],
                    //  check store org event
                    [
                        'class' => 'mockEntityMngr',
                        'method' => 'persist',
                        'call' => $this->at(2),
                        'params' => [$this->isInstanceOf(EventOrganisationMap::class)],
                    ],
                    //  check store site changes to db
                    [
                        'class' => 'mockEntityMngr',
                        'method' => 'persist',
                        'call' => $this->at(3),
                        'params' => [$this->isInstanceOf(Site::class)],
                    ],
                    //  check store map to db
                    [
                        'class' => 'mockEntityMngr',
                        'method' => 'persist',
                        'call' => $this->at(4),
                        'params' => [$this->isInstanceOf(OrganisationSiteMap::class)],
                    ],
                    //  check create notification
                    [
                        'class' => 'mockNotificationSrv',
                        'method' => 'add',
                        'params' => [$this->isInstanceOf(NotificationDto::class)],
                    ],
                ],
                'permissions' => null,
                'expect' => [
                    'result' => true,
                ],
            ],
            //  check rollback
            [
                'method' => 'siteChangeStatus',
                'params' => [
                    'linkId' => self::LINK_ID,
                    'statusCode' => self::STATUS,
                ],
                'repo' => [
                    [
                        'class' => 'mockOrgSiteMapRepo',
                        'method' => 'get',
                        'params' => [self::LINK_ID, OrganisationSiteStatusCode::ACTIVE],
                        'result' => $linkEntity,
                    ],
                    [
                        'class' => 'mockValidator',
                        'method' => 'ValidateUnlink',
                        'params' => [self::STATUS],
                        'result' => null,
                    ],
                    [
                        'class' => 'mockMotTestInProgressSrv',
                        'method' => 'getCountForSite',
                        'params' => [self::SITE_ID],
                        'result' => 0,
                    ],
                    [
                        'class' => 'mockOrgSiteStatusRepo',
                        'method' => 'getByCode',
                        'params' => [self::STATUS],
                        'result' => new OrganisationSiteStatus(),
                    ],
                    [
                        'class' => 'mockEventService',
                        'method' => 'addEvent',
                        'params' => [$this->equalTo(EventTypeCode::UNLINK_AE_SITE)],
                        'result' => new ORMException('org error'),
                    ],
                ],
                'permissions' => null,
                'expect' => [
                    'exception' => [
                        'class' => ORMException::class,
                        'message' => 'org error',
                        'code' => 0,
                    ],
                ],
            ],
        ];
    }

    private function getOrganisation()
    {
        $mockOrg = XMock::of(Organisation::class);
        $mockOrg->expects($this->any())
            ->method('getDesignatedManager')
            ->willReturn((new Person())->setId(self::PERSON_ID));

        $ae = (new AuthorisationForAuthorisedExaminer())
            ->setOrganisation($mockOrg)
            ->setNumber(self::AE_NUMBER);

        $organisation = (new Organisation())
            ->setId(self::ORG_ID)
            ->setAuthorisedExaminer($ae);

        return $organisation;
    }

    private function getSite()
    {
        $organisation = (new Site())
            ->setOrganisation($this->getOrganisation())
            ->setSiteNumber(self::SITE_NUMBER);

        return $organisation;
    }
}
