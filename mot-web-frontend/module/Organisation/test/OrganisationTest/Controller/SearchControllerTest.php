<?php

namespace OrganisationTest\Controller;

use DvsaClient\Mapper\OrganisationMapper;
use DvsaClient\MapperFactory;
use DvsaCommon\Auth\PermissionInSystem;
use CoreTest\Controller\AbstractFrontendControllerTestCase;
use DvsaCommon\Dto\Organisation\OrganisationDto;
use DvsaCommonTest\Bootstrap;
use Dvsa\Mot\Frontend\Test\StubIdentityAdapter;
use DvsaCommonTest\TestUtils\XMock;
use Organisation\Controller\SearchController;
use DvsaCommon\HttpRestJson\Exception\NotFoundException;
use Zend\ServiceManager\ServiceManager;

/**
 * Class SearchControllerTest.
 */
class SearchControllerTest extends AbstractFrontendControllerTestCase
{
    const AE_NUMBER = '1';
    const INVALID_PARAM = '12345';

    /* @var ServiceManager */
    protected $serviceManager;

    public function setUp()
    {
        $this->serviceManager = Bootstrap::getServiceManager();
        $this->serviceManager->setAllowOverride(true);
        $this->setServiceManager($this->serviceManager);

        $this->setController(new SearchController());
        $this->getController()->setServiceLocator($this->serviceManager);

        parent::setUp();
    }

    /**
     * Test has user access to page or not with/out auth and permission.
     *
     * @param array  $params            Action parameters
     * @param array  $permissions       User has permissions
     * @param string $expectedUrl       Expect redirect if failure
     * @param bool   $expectedException Expect Entity Error
     *
     * @dataProvider dataProviderSearchQuestionControllerTestCanAccessHasRight
     */
    public function testSearchControllerCanAccessHasRight(
        $params = [],
        $permissions = [],
        $expectedUrl = null,
        $expectedException = false
    ) {
        $this->setupAuthenticationServiceForIdentity(StubIdentityAdapter::asEnforcement());
        $this->setupAuthorizationService($permissions);

        $this->serviceManager->setService(MapperFactory::class, $this->getMapperFactory($expectedException));

        foreach ($params as $key => $value) {
            $this->request->getPost()->set($key, $value);
            $this->request->setMethod('post');
        }
        $this->getResponseForAction('index');

        if ($expectedUrl) {
            $this->assertRedirectLocation2($expectedUrl);
        } else {
            $this->assertResponseStatus(self::HTTP_OK_CODE);
        }
    }

    public function dataProviderSearchQuestionControllerTestCanAccessHasRight()
    {
        $homeUrl = '/';

        return [
            [[], [PermissionInSystem::MOT_TEST_START], $homeUrl],
            [[], [PermissionInSystem::AUTHORISED_EXAMINER_LIST]],
            [['number' => self::AE_NUMBER], [PermissionInSystem::AUTHORISED_EXAMINER_LIST], '/authorised-examiner/1', false],
            [['number' => self::INVALID_PARAM], [PermissionInSystem::AUTHORISED_EXAMINER_LIST],  null, true],
        ];
    }

    private function getMapperFactory($expectedException)
    {
        $mockMapperFactory = XMock::of(MapperFactory::class);

        $map = [
            [MapperFactory::ORGANISATION, $this->getOrganisationMapperMock($expectedException)],
        ];

        $mockMapperFactory->expects($this->any())
            ->method('__get')
            ->will($this->returnValueMap($map));

        return $mockMapperFactory;
    }

    private function getOrganisationMapperMock($expectedException)
    {
        $mapper = XMock::of(OrganisationMapper::class);

        if ($expectedException) {
            $mapper->expects($this->any())
                ->method('getAuthorisedExaminerByNumber')
                ->with(['number' => self::INVALID_PARAM])
                ->willThrowException(new NotFoundException('', 'GET', new \Exception(), 404));
        } else {
            $mapper->expects($this->any())
                ->method('getAuthorisedExaminerByNumber')
                ->with(['number' => self::AE_NUMBER])
                ->will($this->returnValue((new OrganisationDto())->setId(1)));
        }

        return $mapper;
    }
}
