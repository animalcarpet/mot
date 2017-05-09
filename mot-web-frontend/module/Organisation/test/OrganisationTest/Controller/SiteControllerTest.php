<?php

namespace OrganisationTest\Controller;

use Core\Service\MotFrontendAuthorisationServiceInterface;
use CoreTest\Controller\AbstractFrontendControllerTestCase;
use DvsaClient\Mapper\OrganisationMapper;
use DvsaClient\Mapper\OrganisationSitesMapper;
use DvsaClient\Mapper\SiteMapper;
use DvsaClient\MapperFactory;
use DvsaClient\ViewModel\AbstractFormModel;
use DvsaCommon\Dto\Contact\AddressDto;
use DvsaCommon\Dto\Organisation\AuthorisedExaminerAuthorisationDto;
use DvsaCommon\Dto\Organisation\OrganisationContactDto;
use DvsaCommon\Dto\Organisation\OrganisationDto;
use DvsaCommon\Dto\Organisation\OrganisationSiteLinkDto;
use DvsaCommon\Dto\Site\SiteDto;
use DvsaCommon\Enum\OrganisationContactTypeCode;
use DvsaCommon\Exception\UnauthorisedException;
use DvsaCommon\HttpRestJson\Exception\GeneralRestException;
use DvsaCommon\HttpRestJson\Exception\NotFoundException;
use DvsaCommon\HttpRestJson\Exception\ValidationException;
use DvsaCommon\UrlBuilder\AuthorisedExaminerUrlBuilderWeb;
use DvsaCommon\Utility\ArrayUtils;
use DvsaCommonTest\Bootstrap;
use DvsaCommonTest\TestUtils\TestCasePermissionTrait;
use DvsaCommonTest\TestUtils\XMock;
use DvsaFeature\FeatureToggles;
use Organisation\Controller\SiteController;
use Organisation\Form\AeUnlinkSiteForm;
use PHPUnit_Framework_MockObject_MockObject as MockObj;
use Zend\View\Model\ViewModel;

/**
 * Class SiteControllerTest.
 */
class SiteControllerTest extends AbstractFrontendControllerTestCase
{
    use TestCasePermissionTrait;

    const ORG_ID = 8999;
    const SITE_NUMBER = 7777;
    const LINK_ID = 1111;
    const STATUS = 'UNIT_STATUS';
    const AE_REF = 'B00001';

    protected $serviceManager;

    /** @var MotFrontendAuthorisationServiceInterface|MockObj $auth */
    private $auth;
    /** @var MapperFactory|MockObj $mapper */
    private $mapperFactory;
    /** @var OrganisationMapper|MockObj */
    private $mockOrgMapper;
    /** @var SiteMapper|MockObj */
    private $mockSiteMapper;
    /** @var OrganisationSitesMapper|MockObj */
    private $mockOrgSitesMapper;

    public function setUp()
    {
        $this->serviceManager = Bootstrap::getServiceManager();
        $this->serviceManager->setAllowOverride(true);
        $this->setServiceManager($this->serviceManager);

        $this->auth = XMock::of(MotFrontendAuthorisationServiceInterface::class);
        $this->mapperFactory = $this->getMapperFactory();

        $this->setController(new SiteController($this->auth, $this->mapperFactory));

        $this->getController()->setServiceLocator($this->serviceManager);

        parent::setUp();

        $featureToggle = XMock::of(FeatureToggles::class);
        $this->serviceManager->setService('Feature\FeatureToggles', $featureToggle);
        $this->mockMethod($featureToggle, 'isEnabled', $this->any(), true);
    }

    /**
     * @dataProvider dataProviderTestActionsResult
     */
    public function testActionsResult($method, $action, $params, $mocks, $permissions, $expect)
    {
        $result = null;

        //  mocking methods
        if ($mocks !== null) {
            foreach ($mocks as $mock) {
                $invocation = (isset($mock['call']) ? $mock['call'] : $this->once());
                $mockParams = (isset($mock['params']) ? $mock['params'] : null);

                $this->mockMethod($this->{$mock['class']}, $mock['method'], $invocation, $mock['result'], $mockParams);
            }
        }

        //  check :: permission
        if ($permissions !== null) {
            $this->mockAssertGrantedAtOrganisation($this->auth, current($permissions), key($permissions));
        }

        //  check :: set expected exception
        if (!empty($expect['exception'])) {
            $exception = $expect['exception'];
            $this->setExpectedException($exception['class'], $exception['message']);
        }

        // logical block :: call
        $result = $this->getResultForAction2(
            $method,
            $action,
            ArrayUtils::tryGet($params, 'route'),
            ArrayUtils::tryGet($params, 'get'),
            ArrayUtils::tryGet($params, 'post')
        );

        // logical block :: check
        if (!empty($expect['viewModel'])) {
            $this->assertInstanceOf(ViewModel::class, $result);
            $this->assertResponseStatus(self::HTTP_OK_CODE);
        }

        if (!empty($expect['errors'])) {
            $model = $result->getVariable('model');
            /** @var AbstractFormModel $form */
            $form = $model->getForm();

            foreach ($expect['errors'] as $field => $error) {
                $this->assertEquals($error, $form->getError($field));
            }
        }

        if (!empty($expect['flashError'])) {
            $this->assertEquals(
                $expect['flashError'],
                $this->getController()->flashMessenger()->getCurrentErrorMessages()[0]
            );
        }

        if (!empty($expect['url'])) {
            $this->assertRedirectLocation2($expect['url']);
        }
    }

    public function dataProviderTestActionsResult()
    {
        $orgDto = (new OrganisationDto())
            ->setId(self::ORG_ID)
            ->setAuthorisedExaminerAuthorisation(
                (new AuthorisedExaminerAuthorisationDto())
                    ->setAuthorisedExaminerRef(self::AE_REF)
            )
            ->setContacts(
                [
                    (new OrganisationContactDto())
                        ->setType(OrganisationContactTypeCode::REGISTERED_COMPANY)
                        ->setAddress(new AddressDto()),
                ]
            );

        $siteDto = new SiteDto();

        $linkDto = new OrganisationSiteLinkDto();
        $linkDto
            ->setId(self::LINK_ID)
            ->setOrganisation($orgDto)
            ->setSite($siteDto);

        $unauthException = [
            'class' => UnauthorisedException::class,
            'message' => 'You not have permissions',
        ];

        return [

            //  post: validation error
            [
                'method' => 'post',
                'action' => 'unlink',
                'params' => [
                    'route' => [
                        'linkId' => self::LINK_ID,
                        'id' => self::ORG_ID,
                    ],
                ],
                'mocks' => [
                    [
                        'class' => 'mockOrgSitesMapper',
                        'method' => 'getSiteLink',
                        'params' => [self::LINK_ID],
                        'result' => $linkDto,
                    ],
                    [
                        'class' => 'mockOrgSitesMapper',
                        'method' => 'changeSiteLinkStatus',
                        'params' => [self::LINK_ID],
                        'result' => new ValidationException(
                            '/', 'post', [], 10, [
                                ['field' => AeUnlinkSiteForm::FIELD_STATUS, 'displayMessage' => 'error msg'],
                            ]
                        ),
                    ],
                ],
                'permissions' => null,
                'expect' => [
                    'viewModel' => true,
                    'errors' => [
                        AeUnlinkSiteForm::FIELD_STATUS => 'error msg',
                    ],
                    'debug' => true,
                ],
            ],

            // has access to Add Link
            [
                'method' => 'get',
                'action' => 'link',
                'params' => [
                    'route' => [
                        'id' => self::ORG_ID,
                    ],
                ],
                'mocks' => [],
                'permissions' => null,
                'expect' => [
                    'viewModel' => true,
                ],
            ],
            // Successful link
            [
                'method' => 'post',
                'action' => 'link',
                'params' => [
                    'route' => [
                        'id' => self::ORG_ID,
                    ],
                    'post' => ['siteNumber' => self::SITE_NUMBER],
                ],
                'mocks' => [
                    [
                        'class' => 'mockOrgSitesMapper',
                        'method' => 'createSiteLink',
                        'params' => [self::ORG_ID, self::SITE_NUMBER],
                        'result' => true,
                    ],
                ],
                'permissions' => null,
                'expect' => [
                    'url' => AuthorisedExaminerUrlBuilderWeb::of(self::ORG_ID),
                ],
            ],
            // Fail to link site
            [
                'method' => 'post',
                'action' => 'link',
                'params' => [
                    'route' => [
                        'id' => self::ORG_ID,
                    ],
                    'post' => ['siteNumber' => self::SITE_NUMBER],

                ],
                'mocks' => [
                    [
                        'class' => 'mockOrgSitesMapper',
                        'method' => 'createSiteLink',
                        'params' => [self::ORG_ID, self::SITE_NUMBER],
                        'result' => new ValidationException(
                            '/', 'post', [], 10, [['displayMessage' => 'something wrong']]
                        ),
                    ],
                ],
                'permissions' => null,
                'expect' => [
                    'viewModel' => true,
                ],
            ],

            //  logical group :: test action unlink
            //  get: has access to remove link
            [
                'method' => 'get',
                'action' => 'unlink',
                'params' => [
                    'route' => [
                        'linkId' => self::LINK_ID,
                    ],
                ],
                'mocks' => [
                    [
                        'class' => 'mockOrgSitesMapper',
                        'method' => 'getSiteLink',
                        'params' => [self::LINK_ID],
                        'result' => new NotFoundException('/', 'post', [], 10, 'Link not found'),
                    ],
                ],
                'permissions' => null,
                'expect' => [
                    'exception' => [
                        'class' => NotFoundException::class,
                        'message' => 'Link not found',
                    ],
                ],
            ],
            //  get: has no permission
            [
                'method' => 'get',
                'action' => 'unlink',
                'params' => [
                    'route' => [
                        'linkId' => self::LINK_ID,
                    ],
                ],
                'mocks' => [
                    [
                        'class' => 'mockOrgSitesMapper',
                        'method' => 'getSiteLink',
                        'params' => [self::LINK_ID],
                        'result' => $linkDto,
                    ],
                ],
                'permissions' => [
                    self::ORG_ID => [],
                ],
                'expect' => [
                    'exception' => $unauthException,
                ],
            ],
            //  get: success
            [
                'method' => 'get',
                'action' => 'unlink',
                'params' => [
                    'route' => [
                        'linkId' => self::LINK_ID,
                    ],
                ],
                'mocks' => [
                    [
                        'class' => 'mockOrgSitesMapper',
                        'method' => 'getSiteLink',
                        'params' => [self::LINK_ID],
                        'result' => $linkDto,
                    ],
                ],
                'permissions' => null,
                'expect' => [
                    'viewModel' => true,
                ],
            ],

            //  post: rest exception
            [
                'method' => 'post',
                'action' => 'unlink',
                'params' => [
                    'route' => [
                        'linkId' => self::LINK_ID,
                        'id' => self::ORG_ID,
                    ],
                ],
                'mocks' => [
                    [
                        'class' => 'mockOrgSitesMapper',
                        'method' => 'getSiteLink',
                        'params' => [self::LINK_ID],
                        'result' => $linkDto,
                    ],
                    [
                        'class' => 'mockOrgSitesMapper',
                        'method' => 'changeSiteLinkStatus',
                        'params' => [self::LINK_ID],
                        'result' => new GeneralRestException('/', 'post', [], 10, 'error msg 2'),
                    ],
                ],
                'permissions' => null,
                'expect' => [
                    'viewModel' => true,
                    'flashError' => 'error msg 2',
                ],
            ],

            //  post: success
            [
                'method' => 'post',
                'action' => 'unlink',
                'params' => [
                    'route' => [
                        'linkId' => self::LINK_ID,
                        'id' => self::ORG_ID,
                    ],
                    'post' => [
                        AeUnlinkSiteForm::FIELD_STATUS => self::STATUS,
                    ],
                ],
                'mocks' => [
                    [
                        'class' => 'mockOrgSitesMapper',
                        'method' => 'getSiteLink',
                        'params' => [self::LINK_ID],
                        'result' => $linkDto,
                    ],
                    [
                        'class' => 'mockOrgSitesMapper',
                        'method' => 'changeSiteLinkStatus',
                        'params' => [self::LINK_ID],
                        'result' => null,
                    ],
                ],
                'permissions' => null,
                'expect' => [
                    'url' => AuthorisedExaminerUrlBuilderWeb::of(self::ORG_ID)->toString(),
                ],
            ],
        ];
    }

    private function getMapperFactory()
    {
        $mapperFactory = XMock::of(MapperFactory::class);
        $this->mockOrgMapper = XMock::of(OrganisationMapper::class);
        $this->mockOrgSitesMapper = XMock::of(OrganisationSitesMapper::class);
        $this->mockSiteMapper = XMock::of(SiteMapper::class);

        $org = (new OrganisationDto())
            ->setAuthorisedExaminerAuthorisation(
                new AuthorisedExaminerAuthorisationDto()
            )->setContacts(
                [(new OrganisationContactDto())
                    ->setType(OrganisationContactTypeCode::REGISTERED_COMPANY)
                     ->setAddress(new AddressDto()),
                ]
            );

        $this->mockMethod($this->mockSiteMapper, 'getById', null, new SiteDto(), [self::SITE_NUMBER]);
        $this->mockMethod($this->mockOrgMapper, 'getAuthorisedExaminer', null, $org, [self::ORG_ID]);

        $map = [
            [MapperFactory::ORGANISATION, $this->mockOrgMapper],
            [MapperFactory::SITE, $this->mockSiteMapper],
            [MapperFactory::ORGANISATION_SITE, $this->mockOrgSitesMapper],
        ];

        $this->mockMethod($mapperFactory, '__get', null, $this->returnValueMap($map));

        return $mapperFactory;
    }
}
