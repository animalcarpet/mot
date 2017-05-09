<?php

namespace EventTest\Controller;

use CoreTest\Controller\AbstractFrontendControllerTestCase;
use Dvsa\Mot\Frontend\PersonModule\View\ContextProvider;
use DvsaClient\Mapper\EventMapper;
use DvsaClient\Mapper\SiteMapper;
use DvsaClient\MapperFactory;
use DvsaCommon\Auth\PermissionInSystem;
use DvsaCommon\Dto\Organisation\OrganisationDto;
use DvsaCommon\HttpRestJson\Exception\RestApplicationException;
use DvsaCommonTest\Bootstrap;
use Dvsa\Mot\Frontend\Test\StubIdentityAdapter;
use DvsaCommonTest\TestUtils\XMock;
use DvsaClient\Mapper\OrganisationMapper;
use DvsaClient\Mapper\PersonMapper;
use Event\Controller\EventController;
use Zend\ServiceManager\ServiceManager;
use Zend\Http\PhpEnvironment\Request;
use Zend\Mvc\Router\Http\TreeRouteStack as Router;

/**
 * Class MotTestLogControllerTest.
 */
class EventControllerTest extends AbstractFrontendControllerTestCase
{
    const EVENT_ID = 1;
    const AE_ID = 9;
    const SITE_ID = 1;
    const PERSON_ID = 5;
    const INVALID_PARAM = 9999;

    /** @var ServiceManager */
    protected $serviceManager;

    public function setUp()
    {
        $this->serviceManager = Bootstrap::getServiceManager();
        $this->serviceManager->setAllowOverride(true);
        $this->setServiceManager($this->serviceManager);

        $contextProvider = new ContextProvider(new Router(), new Request());

        $this->setController(new EventController($contextProvider));
        $this->getController()->setServiceLocator($this->serviceManager);

        parent::setUp();
    }

    /**
     * Test has user access to page or not with/out auth and permission.
     *
     * @param string $action                  Action name
     * @param array  $params                  Action parameters
     * @param array  $permissions             User has permissions
     * @param bool   $expectCanAccess         Expect user has or not access to page
     * @param string $expectedUrl             Expect redirect if failure
     * @param bool   $expectedException       Expect Entity Error
     * @param bool   $expectedExceptionDetail Expect Entity Error for the detail
     *
     * @dataProvider dataProviderEventControllerTestCanAccessHasRight
     */
    public function testEventControllerCanAccessHasRight(
        $action,
        $params = [],
        $permissions = [],
        $expectCanAccess = true,
        $expectedUrl = null,
        $expectedException = false,
        $expectedExceptionDetail = false
    ) {
        $this->setupAuthenticationServiceForIdentity(StubIdentityAdapter::asEnforcement());
        $this->setupAuthorizationService($permissions);

        $this->serviceManager->setService(MapperFactory::class, $this->getMapperFactory($expectedException, $expectedExceptionDetail));

        $this->getResponseForAction($action, $params);

        if ($expectedUrl) {
            $this->assertRedirectLocation2($expectedUrl);
        } else {
            $this->assertResponseStatus(self::HTTP_OK_CODE);
        }
    }

    public function dataProviderEventControllerTestCanAccessHasRight()
    {
        $homeUrl = '/';

        return [
            ['list', ['type' => 'ae', 'id' => self::INVALID_PARAM], [PermissionInSystem::LIST_EVENT_HISTORY],
                true, null, true, ],
            ['list', ['type' => 'ae', 'id' => self::AE_ID], [PermissionInSystem::LIST_EVENT_HISTORY], true],
            ['list', ['type' => 'ae', 'id' => self::AE_ID], [PermissionInSystem::MOT_TEST_READ], true, $homeUrl],
            ['list', ['type' => 'ae', 'id' => self::AE_ID], [], false, $homeUrl],
            ['list', ['type' => 'site', 'id' => self::INVALID_PARAM], [PermissionInSystem::LIST_EVENT_HISTORY],
                true, null, true, ],
            ['list', ['type' => 'site', 'id' => self::SITE_ID], [PermissionInSystem::LIST_EVENT_HISTORY], true],
            ['list', ['type' => 'site', 'id' => self::SITE_ID], [PermissionInSystem::MOT_TEST_READ], true, $homeUrl],
            ['list', ['type' => 'site', 'id' => self::SITE_ID], [], false, $homeUrl],
            ['list', ['type' => 'person', 'id' => self::INVALID_PARAM], [PermissionInSystem::LIST_EVENT_HISTORY],
                true, null, true, ],
            ['list', ['type' => 'person', 'id' => self::PERSON_ID], [PermissionInSystem::LIST_EVENT_HISTORY], true],
            ['list', ['type' => 'person', 'id' => self::PERSON_ID], [PermissionInSystem::MOT_TEST_READ], true, $homeUrl],
            ['list', ['type' => 'person', 'id' => self::PERSON_ID], [], false, $homeUrl],
            ['detail', ['type' => 'ae', 'id' => self::AE_ID, 'event-id' => self::INVALID_PARAM],
                [PermissionInSystem::EVENT_READ], true, null, false, true, ],
            ['detail', ['event-id' => self::EVENT_ID], [PermissionInSystem::EVENT_READ], true],
            ['detail', ['event-id' => self::EVENT_ID], [PermissionInSystem::MOT_TEST_READ], true, $homeUrl],
            ['detail', ['event-id' => self::EVENT_ID], [], false, $homeUrl],
        ];
    }

    /**
     * Test has user access to page or not with/out auth and permission.
     *
     * @param bool   $csrf       Request query
     * @param bool   $isShowDate Request query
     * @param array  $dateFrom   Request query
     * @param array  $dateTo     Request query
     * @param string $search     Request query
     *
     * @dataProvider dataProviderEventControllerFormValidation
     */
    public function testEventControllerFormValidation(
        $csrf,
        $isShowDate,
        $dateFrom = null,
        $dateTo = null,
        $search = null
    ) {
        $this->setupAuthenticationServiceForIdentity(StubIdentityAdapter::asEnforcement());
        $this->setupAuthorizationService([PermissionInSystem::LIST_EVENT_HISTORY]);

        $orgDto = new OrganisationDto();
        $orgDto->setId(self::AE_ID);

        $mapper = XMock::of(OrganisationMapper::class);

        $mapper->expects($this->any())
            ->method('getAuthorisedExaminer')
            ->with(self::AE_ID)
            ->will($this->returnValue($orgDto));

        $mockMapperFactory = XMock::of(MapperFactory::class);
        $map = [
            [MapperFactory::ORGANISATION, $mapper],
            [MapperFactory::EVENT, $this->getEventMapperMock(false, false)],
        ];
        $mockMapperFactory->expects($this->any())
            ->method('__get')
            ->will($this->returnValueMap($map));

        $this->request->getQuery()->set('_csrf_token', $csrf);
        $this->request->getQuery()->set('isShowDate', $isShowDate);
        $this->request->getQuery()->set('dateFrom', $dateFrom);
        $this->request->getQuery()->set('dateTo', $dateTo);
        $this->request->getQuery()->set('search', $search);

        $this->serviceManager->setService(MapperFactory::class, $mockMapperFactory);

        $this->getResponseForAction('list', ['type' => 'ae', 'id' => self::AE_ID]);
        $this->assertResponseStatus(self::HTTP_OK_CODE);
    }

    public function dataProviderEventControllerFormValidation()
    {
        return [
            [false, false], // No Search
            [true, false, null, null, 'search'], // Just search filter
            [true, true], // No Date Passed But Form shown
            [true, true, ['Day' => '01', 'Month' => '01', 'Year' => '2014'],
                'dateTo' => ['Day' => '01', 'Month' => '01', 'Year' => '2014'], ], // Valid Date form
            [true, true, ['Day' => '01', 'Month' => '01', 'Year' => '2015'],
                'dateTo' => ['Day' => '01', 'Month' => '01', 'Year' => '2014',
                ], ], // Invalid Date form DateTo After DateFrom
            [true, true, ['Day' => 'aa', 'Month' => 'bb', 'Year' => 'abcd']], // Invalid Date form format incorrect
            [true, true,
                ['Day' => '01', 'Month' => '01', 'Year' => date('Y') + 1], ], // Invalid Date form Date in the future
            [true, true,
                ['Day' => '01', 'Month' => '01', 'Year' => '1899'], ], // Invalid Date form Date to far in the past
        ];
    }

    private function getMapperFactory($expectedException, $expectedExceptionDetail)
    {
        $mockMapperFactory = XMock::of(MapperFactory::class);

        $map = [
            [MapperFactory::ORGANISATION,
                $this->getOrganisationMapperMock($expectedException), ],
            [MapperFactory::SITE,
                $this->getSiteMapperMock($expectedException), ],
            [MapperFactory::PERSON, $this->getPersonMapperMock($expectedException)],
            [MapperFactory::EVENT, $this->getEventMapperMock($expectedException, $expectedExceptionDetail)],
        ];

        $mockMapperFactory->expects($this->any())
            ->method('__get')
            ->will($this->returnValueMap($map));

        return $mockMapperFactory;
    }

    private function getOrganisationMapperMock($expectedException)
    {
        $orgDto = new OrganisationDto();
        $orgDto->setId(self::AE_ID);

        $mapper = XMock::of(OrganisationMapper::class);

        if ($expectedException) {
            $mapper->expects($this->any())
                ->method('getAuthorisedExaminer')
                ->with(self::INVALID_PARAM)
                ->willThrowException(new RestApplicationException('', 'GET', new \Exception(), 404));
        } else {
            $mapper->expects($this->any())
                ->method('getAuthorisedExaminer')
                ->with(self::AE_ID)
                ->will($this->returnValue($orgDto));
        }

        return $mapper;
    }

    private function getSiteMapperMock($expectedException)
    {
        $mapper = XMock::of(SiteMapper::class);

        if ($expectedException) {
            $mapper->expects($this->any())
                ->method('getById')
                ->with(self::INVALID_PARAM)
                ->willThrowException(new RestApplicationException('', 'GET', new \Exception(), 404));
        } else {
            $mapper->expects($this->any())
                ->method('getById')
                ->with(self::SITE_ID)
                ->will($this->returnValue([]));
        }

        return $mapper;
    }

    private function getPersonMapperMock($expectedException)
    {
        $mapper = XMock::of(PersonMapper::class);

        if ($expectedException) {
            $mapper->expects($this->any())
                ->method('getById')
                ->with(self::INVALID_PARAM)
                ->willThrowException(new RestApplicationException('', 'GET', new \Exception(), 404));
        } else {
            $mapper->expects($this->any())
                ->method('getById')
                ->with(self::PERSON_ID)
                ->will($this->returnValue([]));
        }

        return $mapper;
    }

    private function getEventMapperMock($expectedException, $expectedExceptionDetail)
    {
        $mapper = XMock::of(EventMapper::class);

        if ($expectedException) {
            $mapper->expects($this->any())
                ->method('getEventList')
                ->with(self::INVALID_PARAM)
                ->willThrowException(new RestApplicationException('', 'GET', new \Exception(), 404));
        } else {
            $mapper->expects($this->any())
                ->method('getEventList')
                ->will($this->returnValue([]));
        }
        if ($expectedExceptionDetail) {
            $mapper->expects($this->any())
                ->method('getEvent')
                ->with(self::INVALID_PARAM)
                ->willThrowException(new RestApplicationException('', 'GET', new \Exception(), 404));
        } else {
            $mapper->expects($this->any())
                ->method('getEvent')
                ->will($this->returnValue([]));
        }

        return $mapper;
    }
}
