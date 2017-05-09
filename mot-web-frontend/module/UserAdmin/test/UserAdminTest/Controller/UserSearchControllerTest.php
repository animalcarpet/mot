<?php

namespace UserAdminTest\Controller;

use CoreTest\Controller\AbstractFrontendControllerTestCase;
use DvsaClient\Mapper\UserAdminMapper;
use DvsaClient\MapperFactory;
use DvsaCommon\Auth\PermissionInSystem;
use DvsaCommon\Dto\Person\SearchPersonResultDto;
use DvsaCommonTest\Bootstrap;
use Dvsa\Mot\Frontend\Test\StubIdentityAdapter;
use DvsaCommonTest\TestUtils\XMock;
use UserAdmin\Controller\UserSearchController;
use UserAdmin\Service\DateOfBirthFilterService;
use UserAdmin\Service\UserAdminSessionManager;
use Zend\ServiceManager\ServiceManager;
use DvsaCommon\Exception\UnauthorisedException;

/**
 * Class UserSearchControllerTest.
 */
class UserSearchControllerTest extends AbstractFrontendControllerTestCase
{
    /** @var ServiceManager */
    protected $serviceManager;

    /** @var DateOfBirthFilterService $dateOfBirthFilterService */
    private $dateOfBirthFilterService;

    public function setUp()
    {
        $appTestConfig = include getcwd().'/test/test.config.php';
        Bootstrap::init($appTestConfig);

        $this->serviceManager = Bootstrap::getServiceManager();
        $this->serviceManager->setAllowOverride(true);
        $this->setServiceManager($this->serviceManager);

        $this->dateOfBirthFilterService = XMock::of(DateOfBirthFilterService::class);

        $this->setController(new UserSearchController($this->dateOfBirthFilterService));
        $this->getController()->setServiceLocator($this->serviceManager);

        $sessionMock = XMock::of(UserAdminSessionManager::class, ['deleteUserAdminSession']);
        $this->serviceManager->setService(UserAdminSessionManager::class, $sessionMock);

        $sessionMock->expects($this->any())
            ->method('deleteUserAdminSession')
            ->willReturn(true);

        parent::setUp();
    }

    /**
     * Test has user access to page or not with/out auth and permission.
     *
     * @param string $action
     * @param array  $params               Query parameters
     * @param array  $permissions          User has permissions
     * @param bool   $shouldThrowException Expect redirect if failure
     *
     * @dataProvider dataProviderUserProfileControllerTestCanAccessHasRight
     */
    public function testUserProfileControllerCanAccessHasRight(
        $action,
        $params = [],
        $permissions = [],
        $shouldThrowException = true
    ) {
        $this->setupAuthenticationServiceForIdentity(StubIdentityAdapter::asEnforcement());
        $this->setupAuthorizationService($permissions);

        $this->serviceManager->setService(MapperFactory::class, $this->getMapperFactory());

        foreach ($params as $key => $value) {
            $this->request->getQuery()->set($key, $value);
        }

        if ($shouldThrowException) {
            $this->setExpectedException(UnauthorisedException::class);
            $this->getResponseForAction($action);
        } else {
            $this->assertResponseStatus(self::HTTP_OK_CODE);
            $this->getResponseForAction($action);
        }
    }

    public function dataProviderUserProfileControllerTestCanAccessHasRight()
    {
        // Parameters
        /*
         * $action, $params = [], $permissions = [],
         * $expectCanAccess = true, $shouldThrowException = true
        */
        return [
            ['index', [], [PermissionInSystem::MOT_TEST_START], true],
            ['index', [], [PermissionInSystem::USER_SEARCH], false],
            ['results', [], [PermissionInSystem::MOT_TEST_START], true],
            ['results', [], [PermissionInSystem::USER_SEARCH], false],
            ['results', ['username' => 'tester1'], [PermissionInSystem::USER_SEARCH], false],
        ];
    }

    private function getMapperFactory()
    {
        $mockMapperFactory = XMock::of(MapperFactory::class);

        $map = [
            [MapperFactory::USER_ADMIN, $this->getUserAdminMapperMock()],
        ];

        $mockMapperFactory->expects($this->any())
            ->method('__get')
            ->will($this->returnValueMap($map));

        return $mockMapperFactory;
    }

    private function getUserAdminMapperMock()
    {
        $mapper = XMock::of(UserAdminMapper::class);

        $data = [
            'id' => 1,
            'firstName' => 'Bob',
            'lastName' => 'Actor',
            'middleName' => 'Thomas',
            'dateOfBirth' => '1982-02-02',
            'town' => 'Bristol',
            'postcode' => 'BS24 8SR',
            'addressLine1' => 'Address',
            'addressLine2' => '',
            'addressLine3' => '',
            'addressLine4' => '',
            'username' => 'test',
        ];

        $mapper->expects($this->any())
            ->method('searchUsers')
            ->will($this->returnValue([new SearchPersonResultDto($data)]));

        return $mapper;
    }
}
