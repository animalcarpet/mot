<?php
use Application\Data\ApiPersonalDetails;
use Core\TwoStepForm\FormContextInterface;
use Dvsa\Mot\Frontend\PersonModule\Breadcrumbs\CertificatesBreadcrumbs;
use Dvsa\Mot\Frontend\PersonModule\Controller\QualificationDetailsController;
use Dvsa\Mot\Frontend\PersonModule\Form\QualificationDetailsForm;
use Dvsa\Mot\Frontend\PersonModule\Model\QualificationDetailsAddProcess;
use Dvsa\Mot\Frontend\PersonModule\Model\FormContext;
use Dvsa\Mot\Frontend\PersonModule\Routes\QualificationDetailsRoutes;
use Dvsa\Mot\Frontend\PersonModule\Security\PersonProfileGuard;
use Dvsa\Mot\Frontend\PersonModule\Security\PersonProfileGuardBuilder;
use Dvsa\Mot\Frontend\PersonModule\View\ContextProvider;
use Dvsa\Mot\Frontend\SecurityCardModule\Support\TwoFaFeatureToggle;
use Dvsa\Mot\Frontend\SecurityCardModuleTest\Support\TwoFaFeatureToggleTest;
use DvsaAuthorisation\Service\AuthorisationServiceInterface;
use DvsaClient\Mapper\QualificationDetailsMapper;
use DvsaClient\Mapper\SiteMapper;
use DvsaCommon\ApiClient\Person\MotTestingCertificate\Dto\MotTestingCertificateDto;
use DvsaCommon\Enum\AuthorisationForTestingMotStatusCode;
use DvsaCommon\Model\TesterAuthorisation;
use DvsaCommon\Model\TesterGroupAuthorisationStatus;
use DvsaCommonTest\TestUtils\Auth\AuthorisationServiceMock;
use DvsaCommonTest\TestUtils\MethodSpy;
use DvsaCommonTest\TestUtils\XMock;
use Zend\Mvc\Controller\AbstractActionController;
use Dvsa\Mot\ApiClient\Service\AuthorisationService;
use Dvsa\Mot\ApiClient\Resource\Collection;
use Dvsa\Mot\ApiClient\Resource\Item\SecurityCardOrder;

class QualificationDetailsAddProcessTest extends \PHPUnit_Framework_TestCase
{
    const TEST_GROUP_NAME = 'A';
    const USER_ID = 1;
    const USERNAME = 'tester1';

    /** @var QualificationDetailsMapper */
    private $qualificationDetailsMapperMock;
    /** @var  SiteMapper */
    private $siteMapperMock;
    /** @var  CertificatesBreadcrumbs */
    private $qualificationDetailsBreadcrumbsMock;
    /** @var  ApiPersonalDetails */
    private $personalDetailsServiceMock;
    /** @var  PersonProfileGuardBuilder */
    private $personProfileGuardBuilderMock;
    /** @var  ContextProvider */
    private $contextProviderMock;
    /** @var AuthorisationServiceInterface */
    private $authorisationServiceMock;
    /** @var FormContextInterface */
    private $contextMock;
    /** @var  QualificationDetailsAddProcess */
    private $sut;
    /** @var  QualificationDetailsController */
    private $controllerMock;
    /** @var  PersonProfileGuard */
    private $personProfileGuardMock;
    /** @var  QualificationDetailsRoutes */
    private $qualificationDetailsRoutesMock;
    /** @var  AuthorisationService */
    private $authClientMock;

    private $twoFaFeatureToggle;

    private $formData = [
        QualificationDetailsForm::FIELD_VTS_ID => 1,
        QualificationDetailsForm::FIELD_CERT_NUMBER => 123,
        QualificationDetailsForm::FIELD_DATE_YEAR => 2010,
        QualificationDetailsForm::FIELD_DATE_MONTH => 1,
        QualificationDetailsForm::FIELD_DATE_DAY => 1,
    ];

    public function setUp()
    {
        $this->qualificationDetailsMapperMock = XMock::of(QualificationDetailsMapper::class);
        $this->siteMapperMock = Xmock::of(SiteMapper::class);
        $this->qualificationDetailsBreadcrumbsMock = Xmock::of(CertificatesBreadcrumbs::class);
        $this->personalDetailsServiceMock = $this->buildPersonalDetailsServiceMock();

        $this->personProfileGuardMock = Xmock::of(PersonProfileGuard::class);

        $this->personProfileGuardBuilderMock = Xmock::of(PersonProfileGuardBuilder::class);
        $this->personProfileGuardBuilderMock->method('createPersonProfileGuard')
            ->willReturn($this->personProfileGuardMock);

        $this->contextProviderMock = Xmock::of(ContextProvider::class);
        $this->contextMock = Xmock::of(FormContextInterface::class);
        $this->qualificationDetailsRoutesMock = Xmock::of(QualificationDetailsRoutes::class);
        $this->authClientMock = XMock::of(AuthorisationService::class);

        $this->authorisationServiceMock = new AuthorisationServiceMock();

        $this->twoFaFeatureToggle = XMock::of(TwoFaFeatureToggle::class);

        $this->sut = new QualificationDetailsAddProcess(
            $this->qualificationDetailsMapperMock,
            $this->siteMapperMock,
            $this->qualificationDetailsBreadcrumbsMock,
            $this->personalDetailsServiceMock,
            $this->personProfileGuardBuilderMock,
            $this->contextProviderMock,
            $this->qualificationDetailsRoutesMock,
            $this->authClientMock,
            $this->twoFaFeatureToggle
        );

        $this->twoFaFeatureToggle->expects($this->any())->method('isEnabled')->willReturn(true);
        $this->controllerMock = Xmock::of(QualificationDetailsController::class);

        $context = (new FormContext(1, 1, self::TEST_GROUP_NAME, $this->controllerMock));

        $this->sut->setContext($context);
    }

    /**
     * @dataProvider dataProviderTestIsAuthorisedToCreateQualificationDetails
     */
    public function testIsAuthorisedToCreateQualificationDetails($result)
    {
        $this->personProfileGuardMock->method('canCreateQualificationDetails')
            ->willReturn($result);

        $this->assertSame($result, $this->sut->isAuthorised($this->authorisationServiceMock));
    }

    public function dataProviderTestIsAuthorisedToCreateQualificationDetails()
    {
        return [
            [true],
            [false],
        ];
    }

    /**
     * Test if providing correct form data results in createQualificationDetails execution
     * @dataProvider dataProviderTestUpdate
     */
    public function testUpdate($formData, $expected)
    {
        $this->qualificationDetailsMapperMock->method('createQualificationDetails')
            ->willReturn($expected);

        $actual = $this->sut->update($formData);
        $this->assertSame($expected, $actual);
    }

    public function dataProviderTestUpdate()
    {
        return [
            [
                $this->formData,
                true,
            ],
            [
                $this->formData,
                false,
            ],
        ];
    }

    public function testIfExistingCertificateIsRemovedWithinUpdate()
    {
        $this->qualificationDetailsMapperMock->method('getQualificationDetails')
            ->willReturn(new MotTestingCertificateDto());

        $removeMethodSpy = new MethodSpy($this->qualificationDetailsMapperMock, 'removeQualificationDetails');

        $this->sut->update($this->formData);

        $this->assertSame(1, $removeMethodSpy->invocationCount());
    }

    public function testIfFormIsNotPrepopulated()
    {
        $this->assertEmpty($this->sut->getPrePopulatedData());
    }

    public function testAssertConfirmationPageIsRequired()
    {
        $this->assertTrue($this->sut->hasConfirmationPage());
    }

    public function testConfirmationViewModelVariablesArePopulatedCorrectly()
    {
        $this->authClientMock
            ->expects($this->once())
            ->method('getSecurityCardOrders')
            ->with(self::USERNAME)
            ->willReturn(new Collection([], SecurityCardOrder::class));

        $this->personProfileGuardBuilderMock
            ->expects($this->once())
            ->method("getTesterAuthorisation")
            ->with(self::USER_ID)
            ->willReturn($this->withGroupADemoTestNeeded());

        $actual = $this->sut->populateConfirmationPageVariables();

        $this->assertArrayHasKey(QualificationDetailsAddProcess::START_PAGE_ROUTE_VIEW_VARIABLE, $actual);
        $this->assertArrayHasKey(QualificationDetailsAddProcess::CAN_ORDER_CARD_VIEW_VARIABLE, $actual);
        $this->assertArrayHasKey(QualificationDetailsAddProcess::GROUP_NAME_VIEW_VARIABLE, $actual);
        $this->assertEquals($actual[QualificationDetailsAddProcess::GROUP_NAME_VIEW_VARIABLE], self::TEST_GROUP_NAME);
    }

    public function testConfirmationShowSecurityCardOrdersUrlWhenNoOrdersPresent()
    {
        $this->authClientMock
            ->expects($this->once())
            ->method('getSecurityCardOrders')
            ->with(self::USERNAME)
            ->willReturn(new Collection([], SecurityCardOrder::class));

        $this->personProfileGuardBuilderMock
            ->expects($this->once())
            ->method("getTesterAuthorisation")
            ->with(self::USER_ID)
            ->willReturn($this->withGroupADemoTestNeeded());

        $actual = $this->sut->populateConfirmationPageVariables();

        $this->assertTrue($actual[QualificationDetailsAddProcess::CAN_ORDER_CARD_VIEW_VARIABLE]);
    }

    public function testConfirmationHideSecurityCardOrdersUrlWhenOrdersPresent()
    {
        $this->authClientMock
            ->expects($this->once())
            ->method('getSecurityCardOrders')
            ->with(self::USERNAME)
            ->willReturn(new Collection([new \stdClass($this->orderResponseArray())], SecurityCardOrder::class));

        $this->personProfileGuardBuilderMock
            ->expects($this->once())
            ->method("getTesterAuthorisation")
            ->with(self::USER_ID)
            ->willReturn($this->withGroupADemoTestNeeded());

        $actual = $this->sut->populateConfirmationPageVariables();

        $this->assertFalse($actual[QualificationDetailsAddProcess::CAN_ORDER_CARD_VIEW_VARIABLE]);
    }

    public function testConfirmationHideSecurityCardOrdersUrlWhenNotDemoTestNeededWithOrders()
    {
        $this->authClientMock
            ->expects($this->once())
            ->method('getSecurityCardOrders')
            ->with(self::USERNAME)
            ->willReturn(new Collection([new \stdClass($this->orderResponseArray())], SecurityCardOrder::class));

        $this->personProfileGuardBuilderMock
            ->expects($this->once())
            ->method("getTesterAuthorisation")
            ->willReturn($this->withFullyAuthorisedTester());

        $actual = $this->sut->populateConfirmationPageVariables();

        $this->assertFalse($actual[QualificationDetailsAddProcess::CAN_ORDER_CARD_VIEW_VARIABLE]);
    }

    protected function buildPersonalDetailsServiceMock()
    {
        $personalDetailsServiceMock = Xmock::of(ApiPersonalDetails::class);
        $personalDetailsServiceMock->method('getPersonalDetailsData')
            ->willReturn([
                'id'                   => self::USER_ID,
                'firstName'            => 'foo',
                'middleName'           => 'bar',
                'surname'              => 'baz',
                'username'             =>  self::USERNAME,
                'dateOfBirth'          => '1979-12-20',
                'title'                => 'Mr',
                'gender'               => 'male',
                'addressLine1'         => 'foo',
                'addressLine2'         => 'foo',
                'addressLine3'         => 'foo',
                'town'                 => 'foo',
                'postcode'             => 'AA11 1AA',
                'email'                => 'foo',
                'emailConfirmation'    => null,
                'phone'                => 1234,
                'drivingLicenceNumber' => 'foo',
                'drivingLicenceRegion' => 'bar',
                'positions'            => [],
                'roles'                => $this->setMockRoles(),
            ]);

        return $personalDetailsServiceMock;
    }

    private function withGroupADemoTestNeeded()
    {
        $demoTestStatus = new TesterGroupAuthorisationStatus(AuthorisationForTestingMotStatusCode::DEMO_TEST_NEEDED, "name");
        $status = new TesterGroupAuthorisationStatus(AuthorisationForTestingMotStatusCode::QUALIFIED, "name");
        return new TesterAuthorisation($demoTestStatus, $status);
    }

    private function withFullyAuthorisedTester()
    {
        $status = new TesterGroupAuthorisationStatus(AuthorisationForTestingMotStatusCode::QUALIFIED, "name");
        return new TesterAuthorisation($status, $status);
    }

    private function setMockRoles()
    {
        return [
            'system'        => [
                'roles' => ['USER'],
            ],
            'organisations' => [10 => [
                'name'    => 'testing',
                'number'  => 'VTESTING',
                'address' => '34 Test Road',
                'roles'   => ['AEDM'],
            ]],
            'sites'         => [20 => [
                'name'    => 'testing',
                'number'  => 'VTESTING',
                'address' => '34 Test Road',
                'roles'   => ['TESTER'],
            ]]
        ];
    }

    private function orderResponseArray()
    {
        return [
            "submittedOn" => "2014-05-25",
            "fullName" => "AUTH_INT_YOTURWDKSPFUMKBPMGMU AUTH_INT_YOTURWDKSPFUMKBPMGMU",
            "recipientName" => "",
            "addressLine1" => "9f1341",
            "addressLine2" => "5 Uncanny St",
            "addressLine3" => "fake address line 3",
            "postcode" => "L1 1PQ",
            "town" => "Liverpool"
        ];
    }
}