<?php

namespace OrganisationApiTest\Service;

use DvsaAuthorisation\Service\AuthorisationServiceInterface;
use DvsaCommon\Auth\PermissionAtOrganisation;
use DvsaCommon\Exception\UnauthorisedException;
use DvsaCommonApi\Service\Exception\NotFoundException;
use DvsaCommonApiTest\Service\AbstractServiceTestCase;
use DvsaCommonTest\TestUtils\XMock;
use DvsaEntities\Entity\Organisation;
use DvsaEntities\Repository\OrganisationRepository;
use OrganisationApi\Service\Mapper\SiteMapper;
use OrganisationApi\Service\SiteService;
use PHPUnit_Framework_MockObject_MockObject as MockObj;

class SiteServiceTest extends AbstractServiceTestCase
{
    const ORG_ID = 9999;

    /**
     * @var AuthorisationServiceInterface|MockObj
     */
    private $mockAuthService;
    /**
     * @var OrganisationRepository|MockObj
     */
    private $mockOrgRepo;
    /**
     * @var SiteMapper|MockObj
     */
    private $mockSiteMapper;
    /**
     * @var SiteService
     */
    private $siteService;

    public function setup()
    {
        $this->mockAuthService = $this->getMockAuthorizationService();
        $this->mockSiteMapper = XMock::of(SiteMapper::class);
        $this->mockOrgRepo = $this->getMockRepository(OrganisationRepository::class);

        $this->siteService = new SiteService(
            $this->mockAuthService,
            $this->mockOrgRepo,
            $this->mockSiteMapper
        );
    }

    /**
     * @dataProvider dataProviderTestMethodsPermissionsAndResults
     */
    public function testGetDataMethodsPermissionsAndResults($method, $params, $repoMocks, $permissions, $expect)
    {
        if ($repoMocks !== null) {
            foreach ($repoMocks as $repo) {
                $this->mockMethod(
                    $this->{$repo['class']}, $repo['method'], $this->once(), $repo['result'], $repo['params']
                );
            }
        }

        //  --  check permission    --
        if ($permissions !== null) {
            $this->assertGrantedAtOrganisation($this->mockAuthService, $permissions, $params['orgId']);
        }

        //  --  set expected exception  --
        if (!empty($expect['exception'])) {
            $exception = $expect['exception'];
            $this->setExpectedException($exception['class'], $exception['message']);
        }

        //  --  call and check result --
        $actual = XMock::invokeMethod($this->siteService, $method, $params);

        $this->assertSame($expect['result'], $actual);
    }

    public function dataProviderTestMethodsPermissionsAndResults()
    {
        $orgEntity = new Organisation();

        $unauthException = [
            'class' => UnauthorisedException::class,
            'message' => 'You not have permissions',
        ];

        return [
            //  getListForOrganisation :: no permission
            [
                'method' => 'getListForOrganisation',
                'params' => [
                    'orgId' => self::ORG_ID,
                ],
                'repo' => null,
                'permissions' => [],
                'expect' => [
                    'exception' => $unauthException,
                ],
            ],
            //  getListForOrganisation :: invalid org id
            [
                'method' => 'getListForOrganisation',
                'params' => [
                    'orgId' => self::ORG_ID,
                ],
                'repos' => [
                    [
                        'class' => 'mockOrgRepo',
                        'method' => 'get',
                        'params' => [self::ORG_ID],
                        'result' => new NotFoundException('Organisation'),
                    ],
                ],
                'permissions' => [PermissionAtOrganisation::VEHICLE_TESTING_STATION_LIST_AT_AE],
                'expect' => [
                    'exception' => [
                        'class' => NotFoundException::class,
                        'message' => 'Organisation not found',
                    ],
                ],
            ],
            //  getListForOrganisation :: success
            [
                'method' => 'getListForOrganisation',
                'params' => [
                    'orgId' => self::ORG_ID,
                ],
                'repos' => [
                    [
                        'class' => 'mockOrgRepo',
                        'method' => 'get',
                        'params' => [self::ORG_ID],
                        'result' => $orgEntity,
                    ],
                    [
                        'class' => 'mockSiteMapper',
                        'method' => 'manyToDto',
                        'params' => [$orgEntity->getSites()],
                        'result' => ['sitesDtos'],
                    ],
                ],
                'permissions' => [PermissionAtOrganisation::VEHICLE_TESTING_STATION_LIST_AT_AE],
                'expect' => [
                    'result' => ['sitesDtos'],
                ],
            ],
        ];
    }
}
