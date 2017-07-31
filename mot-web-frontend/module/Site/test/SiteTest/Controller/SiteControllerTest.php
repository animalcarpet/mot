<?php

namespace SiteTest\Controller;

use Application\Service\CatalogService;
use Core\Catalog\BusinessRole\BusinessRoleCatalog;
use Core\Service\MotFrontendAuthorisationServiceInterface;
use CoreTest\Controller\AbstractFrontendControllerTestCase;
use Dvsa\Mot\Frontend\AuthenticationModule\Model\MotFrontendIdentityInterface;
use Dvsa\Mot\Frontend\PersonModule\View\ContextProvider;
use DvsaClient\Mapper\EquipmentMapper;
use DvsaClient\Mapper\MotTestInProgressMapper;
use DvsaClient\Mapper\SiteMapper;
use DvsaClient\MapperFactory;
use DvsaCommon\Auth\Assertion\ViewVtsTestQualityAssertion;
use DvsaCommon\Auth\MotIdentityProviderInterface;
use DvsaCommon\Configuration\MotConfig;
use DvsaCommon\Constants\FacilityTypeCode;
use DvsaCommon\Dto\Contact\AddressDto;
use DvsaCommon\Dto\Contact\EmailDto;
use DvsaCommon\Dto\Contact\PhoneDto;
use DvsaCommon\Dto\Site\FacilityDto;
use DvsaCommon\Dto\Site\FacilityTypeDto;
use DvsaCommon\Dto\Site\SiteContactDto;
use DvsaCommon\Dto\Site\VehicleTestingStationDto;
use DvsaCommon\Enum\EquipmentModelStatusCode;
use DvsaCommon\Enum\PhoneContactTypeCode;
use DvsaCommon\Enum\SiteContactTypeCode;
use DvsaCommon\Enum\SiteTypeCode;
use DvsaCommon\HttpRestJson\Exception\ValidationException;
use DvsaCommon\UrlBuilder\VehicleTestingStationUrlBuilderWeb;
use DvsaCommon\Utility\ArrayUtils;
use DvsaCommonTest\Bootstrap;
use DvsaCommonTest\TestUtils\XMock;
use Site\Action\SiteTestQualityAction;
use Site\Action\UserTestQualityAction;
use Site\Controller\SiteController;
use Site\Form\VtsCreateForm;
use Site\Form\VtsUpdateTestingFacilitiesForm;
use Site\Service\SiteBreadcrumbsBuilder;
use Site\ViewModel\VehicleTestingStation\VtsFormViewModel;
use Zend\Session\Container;
use Zend\View\Model\ViewModel;
use Dvsa\Mot\Frontend\TestQualityInformation\Breadcrumbs\TesterTqiComponentsAtSiteBreadcrumbs;

/**
 * Class SiteControllerTest.
 *
 * Tests for adding and editing VTS and viewing overview
 */
class SiteControllerTest extends AbstractFrontendControllerTestCase
{
    const SESSION_KEY = 'test_sessKey';

    const SITE_ID = 9;
    const SITE_NR = 'S000001';
    const SITE_NAME = 'Site Name';

    private $config;

    /** @var MotFrontendAuthorisationServiceInterface|MockObj $auth */
    private $auth;
    /** @var MapperFactory|MockObj $mapper */
    private $mapper;
    /** @var MotIdentityProviderInterface|MockObj $identity */
    private $identity;
    /** @var CatalogService|MockObj $identity */
    private $catalog;
    private $identityInterface;
    private $vehicleTestingStationMapperMock;
    /**
     * @var Container
     */
    private $mockSession;
    /** @var SiteTestQualityAction */
    private $mockSiteTestQualityAction;
    /** @var ViewVtsTestQualityAssertion */
    private $mockViewVtsTestQualityAssertion;
    /** @var UserTestQualityAction */
    private $mockUserTestQualityAction;

    protected function setUp()
    {
        $serviceManager = Bootstrap::getServiceManager();
        $serviceManager->setAllowOverride(true);
        $this->setServiceManager($serviceManager);

        $this->auth = XMock::of(MotFrontendAuthorisationServiceInterface::class);
        $this->mapper = $this->getMapperFactory();
        $this->identity = XMock::of(MotIdentityProviderInterface::class);
        $this->catalog = XMock::of(CatalogService::class);
        $this->identityInterface = XMock::of(MotFrontendIdentityInterface::class);
        $this->mockSession = XMock::of(Container::class);
        $this->mockSiteTestQualityAction = XMock::of(SiteTestQualityAction::class);
        $this->mockViewVtsTestQualityAssertion = XMock::of(ViewVtsTestQualityAssertion::class);
        $this->mockUserTestQualityAction = XMock::of(UserTestQualityAction::class);

        /** @var BusinessRoleCatalog|\PHPUnit_Framework_MockObject_MockObject $businessRoleCatalog */
        $businessRoleCatalog = XMock::of(BusinessRoleCatalog::class);
        $siteBreadcrumbsBuilder = XMock::of(SiteBreadcrumbsBuilder::class);
        $siteBreadcrumbsBuilder->method('getVtsBreadcrumb')->willReturn([]);
        $siteBreadcrumbsBuilder->method('getAeBreadcrumb')->willReturn([]);

        $this->setController(
            new SiteController(
                $this->auth, $this->mapper, $this->identity, $this->catalog, $this->mockSession, $businessRoleCatalog,
                $this->mockSiteTestQualityAction, $this->mockUserTestQualityAction, $this->mockViewVtsTestQualityAssertion,
                XMock::of(ContextProvider::class), XMock::of(TesterTqiComponentsAtSiteBreadcrumbs::class),
                $siteBreadcrumbsBuilder
            )
        );

        $this->getController()->setServiceLocator($serviceManager);

        parent::setUp();

        $this->mockConfig();
        $serviceManager->setService(MotConfig::class, $this->config);

        $this->identity->expects($this->any())
            ->method('getIdentity')
            ->willReturn($this->identityInterface);

        $this->catalog
            ->expects($this->any())
            ->method('getEquipmentModelStatuses')
            ->willReturn(
                [
                    json_decode(
                        '"equipmentModelStatus": [
                            {
                                "id": 1,
                                "code": "'.EquipmentModelStatusCode::APPROVED.'",
                                "name": "Approved"
                            },
                            {
                                "id": 2,
                                "code": "'.EquipmentModelStatusCode::NOT_INSTALLABLE.'",
                                "name": "Not Installable"
                            },
                            {
                                "id": 3,
                                "code": "'.EquipmentModelStatusCode::WITHDRAWN.'",
                                "name": "Withdrawn"
                            }
                        ]'
                    ),
                ]
            );
    }

    /**
     * @dataProvider dataProviderTestActionsResult
     */
    public function testActionsResult($method, $action, $params, $mocks, $expect)
    {
        $result = null;

        //  logical block :: mock
        //  enable any feature
        //  mocking methods
        if ($mocks !== null) {
            foreach ($mocks as $mock) {
                $invocation = (isset($mock['call']) ? $mock['call'] : $this->once());
                $mockParams = (isset($mock['params']) ? $mock['params'] : null);

                $this->mockMethod($this->{$mock['class']}, $mock['method'], $invocation, $mock['result'], $mockParams);
            }
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

        if (!empty($expect['viewForm']) || !empty($expect['errors'])) {
            /** @var VtsFormViewModel $module */
            /** @var VtsCreateForm $form */
            $model = $result->getVariable('model');

            if ($model instanceof VtsFormViewModel) {
                $form = $model->getForm();
            } else {
                $form = $result->getVariable('form');
            }

            if (!empty($expect['viewForm'])) {
                $expectForm = $expect['viewForm'];
                $expectFormObj = $expectForm['obj'];

                $this->assertInstanceOf(get_class($expectFormObj), $form);

                if ($expectForm['isSame']) {
                    $this->assertSame($expectFormObj, $form);
                } else {
                    $this->assertNotSame($expectFormObj, $form);
                }
            }

            if (!empty($expect['errors'])) {
                foreach ($expect['errors'] as $field => $error) {
                    $this->assertEquals($error, $form->getError($field));
                }
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
        /** @var VtsCreateForm|MockObj $formVtsCreate */
        $formVtsCreate = XMock::of(VtsCreateForm::class);
        $this->mockMethod($formVtsCreate, 'toDto', null, $this->getVehicleTestingStationDto());

        $formTestingFacilitiesUpdate = new VtsUpdateTestingFacilitiesForm();
        $formTestingFacilitiesUpdate->fromDto($this->getVehicleTestingStationDto());

        return [
            ['get', 'index', ['route' => ['id' => self::SITE_ID]], [], ['viewModel' => true]],
            ['get', 'create', [], [], ['viewModel' => true]],
            //  get form from session; form not valid;
            [
                'method' => 'post',
                'action' => 'create',
                'params' => [
                    'get' => [
                        SiteController::SESSION_KEY => self::SESSION_KEY,
                    ],
                ],
                'mocks' => [
                    [
                        'class' => 'mockSession',
                        'method' => 'offsetGet',
                        'params' => [self::SESSION_KEY],
                        'result' => $formVtsCreate,
                    ],
                    [
                        'class' => 'vehicleTestingStationMapperMock',
                        'method' => 'validate',
                        'params' => $this->getVehicleTestingStationDto(),
                        'result' => new ValidationException(
                            '/', 'post', [], 10, [['displayMessage' => 'something wrong']]
                        ),
                    ],
                ],
                'expect' => [
                    'viewModel' => true,
                    'viewForm' => [
                        'obj' => $formVtsCreate,
                        'isSame' => true,
                    ],
                ],
            ],
            //  logical block :: create confirmation action
            //  form not in session; redirect ot create form
            [
                'method' => 'get',
                'action' => 'confirmation',
                'params' => [
                    'get' => [
                        SiteController::SESSION_KEY => self::SESSION_KEY,
                    ],
                ],
                'mocks' => [],
                'expect' => [
                    'url' => VehicleTestingStationUrlBuilderWeb::create(),
                ],
            ],
            // form in session; show confirm page
            [
                'method' => 'get',
                'action' => 'confirmation',
                'params' => [
                    'get' => [
                        SiteController::SESSION_KEY => self::SESSION_KEY,
                    ],
                ],
                'mocks' => [
                    [
                        'class' => 'mockSession',
                        'method' => 'offsetGet',
                        'params' => [self::SESSION_KEY],
                        'result' => $formVtsCreate,
                    ],
                ],
                'expect' => [
                    'viewModel' => true,
                    'viewForm' => [
                        'obj' => $formVtsCreate,
                        'isSame' => true,
                    ],
                ],
            ],
            // form in session; post successful; redirect to AE view
            [
                'method' => 'post',
                'action' => 'confirmation',
                'params' => [
                    'get' => [
                        SiteController::SESSION_KEY => self::SESSION_KEY,
                    ],
                ],
                'mocks' => [
                    [
                        'class' => 'mockSession',
                        'method' => 'offsetGet',
                        'params' => [self::SESSION_KEY],
                        'result' => $formVtsCreate,
                    ],
                    [
                        'class' => 'mockSession',
                        'method' => 'offsetUnset',
                        'params' => [self::SESSION_KEY],
                        'result' => null,
                    ],
                    [
                        'class' => 'vehicleTestingStationMapperMock',
                        'method' => 'create',
                        'params' => [$formVtsCreate->toDto()],
                        'result' => ['id' => self::CURRENT_VTS_ID],
                    ],
                ],
                'expect' => [
                    'url' => VehicleTestingStationUrlBuilderWeb::byId(self::CURRENT_VTS_ID),
                ],
            ],
            [
                'method' => 'get',
                'action' => 'index',
                'params' => ['route' => ['id' => null]],
                'mocks' => [],
                'expect' => [
                    'exception' => [
                        'class' => \Exception::class,
                        'message' => SiteController::ERR_MSG_INVALID_SITE_ID_OR_NR,
                    ],
                ],
            ],
            [
                'method' => 'get',
                'action' => 'configureBrakeTestDefaults',
                'params' => ['route' => ['id' => null]],
                'mocks' => [],
                'expect' => [
                    'exception' => [
                        'class' => \Exception::class,
                        'message' => SiteController::ERR_MSG_INVALID_SITE_ID_OR_NR,
                    ],
                ],
            ],
            [
                'method' => 'get',
                'action' => 'configureBrakeTestDefaults',
                'params' => ['route' => ['id' => self::SITE_ID]],
                'mocks' => [],
                'expect' => [
                    'viewModel' => true,
                ],
            ],
            [
                'method' => 'get',
                'action' => 'testingFacilities',
                'params' => [
                    'route' => [
                        'id' => self::SITE_ID,
                    ],
                ],
                'mocks' => [],
                'expect' => [
                    'viewModel' => true,
                    'viewForm' => [
                        'obj' => $formTestingFacilitiesUpdate,
                        'isSame' => false,
                    ],
                ],
            ],
            [
                'method' => 'get',
                'action' => 'testingFacilities',
                'params' => [
                    'get' => [
                        SiteController::SESSION_KEY => self::SESSION_KEY,
                    ],
                    'route' => [
                        'id' => self::SITE_ID,
                    ],
                ],
                'mocks' => [
                    [
                        'class' => 'mockSession',
                        'method' => 'offsetGet',
                        'params' => [self::SESSION_KEY],
                        'result' => $formTestingFacilitiesUpdate,
                    ],
                ],
                'expect' => [
                    'viewModel' => true,
                    'viewForm' => [
                        'obj' => $formTestingFacilitiesUpdate,
                        'isSame' => true,
                    ],
                ],
            ],
            //  logical block :: update testing facilities confirmation action
            //  form not in session; redirect to site summary page
            [
                'method' => 'get',
                'action' => 'testingFacilitiesConfirmation',
                'params' => [
                    'get' => [
                        SiteController::SESSION_KEY => self::SESSION_KEY,
                    ],
                    'route' => [
                        'id' => self::SITE_ID,
                    ],
                ],
                'mocks' => [],
                'expect' => [
                    'url' => VehicleTestingStationUrlBuilderWeb::byId(self::SITE_ID),
                ],
            ],
            // form in session; show confirm page
            [
                'method' => 'get',
                'action' => 'testingFacilitiesConfirmation',
                'params' => [
                    'get' => [
                        SiteController::SESSION_KEY => self::SESSION_KEY,
                    ],
                    'route' => [
                        'id' => self::SITE_ID,
                    ],
                ],
                'mocks' => [
                    [
                        'class' => 'mockSession',
                        'method' => 'offsetGet',
                        'params' => [self::SESSION_KEY],
                        'result' => $formTestingFacilitiesUpdate,
                    ],
                ],
                'expect' => [
                    'viewModel' => true,
                    'viewForm' => [
                        'obj' => $formTestingFacilitiesUpdate,
                        'isSame' => true,
                    ],
                ],
            ],
            // form in session; post successful; redirect to AE view
            [
                'method' => 'post',
                'action' => 'testingFacilitiesConfirmation',
                'params' => [
                    'get' => [
                        SiteController::SESSION_KEY => self::SESSION_KEY,
                    ],
                    'route' => [
                        'id' => self::SITE_ID,
                    ],
                ],
                'mocks' => [
                    [
                        'class' => 'mockSession',
                        'method' => 'offsetGet',
                        'params' => [self::SESSION_KEY],
                        'result' => $formTestingFacilitiesUpdate,
                    ],
                    [
                        'class' => 'mockSession',
                        'method' => 'offsetUnset',
                        'params' => [self::SESSION_KEY],
                        'result' => null,
                    ],
                    [
                        'class' => 'vehicleTestingStationMapperMock',
                        'method' => 'updateTestingFacilities',
                        'params' => [
                            self::SITE_ID,
                            $formTestingFacilitiesUpdate->toDto(),
                        ],
                        'result' => ['id' => self::SITE_ID],
                    ],
                ],
                'expect' => [
                    'url' => VehicleTestingStationUrlBuilderWeb::byId(self::SITE_ID),
                ],
            ],
        ];
    }

    private function getVehicleTestingStationMapperMock()
    {
        $dto = (new VehicleTestingStationDto())
            ->setName(self::SITE_NAME)
            ->setId(self::SITE_ID)
            ->setContacts(
                [
                    (new SiteContactDto())
                        ->setType(SiteContactTypeCode::BUSINESS)
                        ->setAddress(new AddressDto()),
                ]
            );

        $this->vehicleTestingStationMapperMock = XMock::of(SiteMapper::class);

        $this->vehicleTestingStationMapperMock->expects($this->any())
            ->method('getById')
            ->with(self::SITE_ID)
            ->willReturn($dto);

        $this->vehicleTestingStationMapperMock->expects($this->any())
            ->method('getByNumber')
            ->with(self::SITE_NR)
            ->willReturn($dto);

        return $this->vehicleTestingStationMapperMock;
    }

    private function getEquipmentMapperMock()
    {
        $equipmentMapper = XMock::of(EquipmentMapper::class);

        $equipmentMapper->expects($this->any())
            ->method('fetchAllForVts')
            ->will(
                $this->returnValue([])
            );

        return $equipmentMapper;
    }

    private function getTestsInProgressMapperMock()
    {
        $equipmentMapper = XMock::of(MotTestInProgressMapper::class);

        $equipmentMapper->expects($this->any())
            ->method('fetchAllForVts')
            ->will(
                $this->returnValue([])
            );

        return $equipmentMapper;
    }

    private function getMapperFactory()
    {
        $map = [
            [MapperFactory::SITE, $this->getVehicleTestingStationMapperMock()],
            [MapperFactory::EQUIPMENT, $this->getEquipmentMapperMock()],
            [MapperFactory::MOT_TEST_IN_PROGRESS, $this->getTestsInProgressMapperMock()],
        ];

        $mapperFactoryMock = XMock::of(MapperFactory::class);

        $mapperFactoryMock->expects($this->any())
            ->method('__get')
            ->will($this->returnValueMap($map));

        return $mapperFactoryMock;
    }

    /**
     * @return $obj
     */
    private function cloneObj($obj)
    {
        return clone $obj;
    }

    private function getVehicleTestingStationDto()
    {
        $address = (new AddressDto())
            ->setAddressLine1('test_Addr1')
            ->setPostcode('test_Postcode')
            ->setTown('test_Town');
        $phone = (new PhoneDto())
            ->setIsPrimary(true)
            ->setContactType(PhoneContactTypeCode::BUSINESS)
            ->setNumber('test_Phone1');
        $email = (new EmailDto())
            ->setEmail('test_Email1@toto.com')
            ->setEmailConfirm('test_Email1@toto.com')
            ->setIsSupplied(true)
            ->setIsPrimary(true);
        $contact = new SiteContactDto();
        $contact
            ->setType(SiteContactTypeCode::BUSINESS)
            ->setAddress($address)
            ->setEmails([$email])
            ->setPhones([$phone]);

        //  logical block :: fill facilities
        $facilities = [
            (new FacilityDto())->setType((new FacilityTypeDto())->setCode(FacilityTypeCode::ONE_PERSON_TEST_LANE)),
            (new FacilityDto())->setType((new FacilityTypeDto())->setCode(FacilityTypeCode::TWO_PERSON_TEST_LANE)),
        ];

        //  logical block :: assemble dto
        $siteDto = new VehicleTestingStationDto();
        $siteDto
            ->setIsOptlSelected(true)
            ->setIsTptlSelected(true)
            ->setName(self::SITE_NAME)
            ->setType(SiteTypeCode::VEHICLE_TESTING_STATION)
            ->setFacilities($facilities)
            ->setIsDualLanguage(false)
            ->setTestClasses([1, 2])
            ->setContacts([$contact]);

        return $siteDto;
    }

    protected function mockConfig()
    {
        $this->config = $this->getMockBuilder(MotConfig::class)->disableOriginalConstructor()->getMock();
        $returnMap = [
            ['site_assessment', 'green', 'start', 0],
            ['site_assessment', 'amber', 'start', 324.11],
            ['site_assessment', 'red', 'start', 459.21],
        ];

        $this->config->expects($this->any())->method('get')->will($this->returnValueMap($returnMap));
    }
}
