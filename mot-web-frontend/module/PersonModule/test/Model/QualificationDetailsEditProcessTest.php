<?php
use Application\Data\ApiPersonalDetails;
use Core\TwoStepForm\FormContextInterface;
use Dvsa\Mot\Frontend\PersonModule\Breadcrumbs\CertificatesBreadcrumbs;
use Dvsa\Mot\Frontend\PersonModule\Controller\QualificationDetailsController;
use Dvsa\Mot\Frontend\PersonModule\Form\QualificationDetailsForm;
use Dvsa\Mot\Frontend\PersonModule\Model\FormContext;
use Dvsa\Mot\Frontend\PersonModule\Model\QualificationDetailsEditProcess;
use Dvsa\Mot\Frontend\PersonModule\Routes\QualificationDetailsRoutes;
use Dvsa\Mot\Frontend\PersonModule\Security\PersonProfileGuard;
use Dvsa\Mot\Frontend\PersonModule\Security\PersonProfileGuardBuilder;
use Dvsa\Mot\Frontend\PersonModule\View\ContextProvider;
use DvsaAuthorisation\Service\AuthorisationServiceInterface;
use DvsaClient\Mapper\QualificationDetailsMapper;
use DvsaClient\Mapper\SiteMapper;
use DvsaCommon\ApiClient\Person\MotTestingCertificate\Dto\MotTestingCertificateDto;
use DvsaCommonTest\TestUtils\Auth\AuthorisationServiceMock;
use DvsaCommonTest\TestUtils\XMock;
use Zend\Mvc\Controller\AbstractActionController;

class QualificationDetailsEditProcessTest extends \PHPUnit_Framework_TestCase
{
    const DATE_OF_QUALIFICATION = '10-10-2000';
    const CERTIFICATE_NUMBER = 'W123';
    const VTS_ID = 1;
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
    /** @var  QualificationDetailsEditProcess */
    private $sut;
    /** @var  QualificationDetailsController */
    private $controllerMock;
    /** @var  PersonProfileGuard */
    private $personProfileGuardMock;
    /** @var  QualificationDetailsRoutes */
    private $qualificationDetailsRoutesMock;

    private $formData = [
        QualificationDetailsForm::FIELD_VTS_ID => self::VTS_ID,
        QualificationDetailsForm::FIELD_CERT_NUMBER => self::CERTIFICATE_NUMBER,
        QualificationDetailsForm::FIELD_DATE_DAY => '10',
        QualificationDetailsForm::FIELD_DATE_MONTH => '10',
        QualificationDetailsForm::FIELD_DATE_YEAR => '2000',
    ];

    public function setUp()
    {
        $this->qualificationDetailsMapperMock = XMock::of(QualificationDetailsMapper::class);
        $this->qualificationDetailsMapperMock->method('getQualificationDetails')
            ->willReturn((new MotTestingCertificateDto())
                ->setSiteNumber(self::VTS_ID)
                ->setDateOfQualification(self::DATE_OF_QUALIFICATION)
                ->setCertificateNumber(self::CERTIFICATE_NUMBER)
        );
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

        $this->authorisationServiceMock = new AuthorisationServiceMock();

        $this->sut = new QualificationDetailsEditProcess(
            $this->qualificationDetailsMapperMock,
            $this->siteMapperMock,
            $this->qualificationDetailsBreadcrumbsMock,
            $this->personalDetailsServiceMock,
            $this->personProfileGuardBuilderMock,
            $this->contextProviderMock,
            $this->qualificationDetailsRoutesMock
        );

        $this->controllerMock = Xmock::of(QualificationDetailsController::class);

        $context = (new FormContext(1, 1, 'A', $this->controllerMock));

        $this->sut->setContext($context);
    }

    /**
     * @dataProvider dataProviderTestIsAuthorisedToUpdateQualificationDetails
     */
    public function testIsAuthorisedToUpdateQualificationDetails($result)
    {
        $this->personProfileGuardMock->method('canUpdateQualificationDetails')
            ->willReturn($result);

        $this->assertSame($result, $this->sut->isAuthorised($this->authorisationServiceMock));
    }

    public function dataProviderTestIsAuthorisedToUpdateQualificationDetails()
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
        $this->qualificationDetailsMapperMock->method('updateQualificationDetails')
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

    public function testIfFormIsPrepopulated()
    {
        $this->assertSame($this->formData, $this->sut->getPrePopulatedData());
    }

    protected function buildPersonalDetailsServiceMock()
    {
        $personalDetailsServiceMock = Xmock::of(ApiPersonalDetails::class);
        $personalDetailsServiceMock->method('getPersonalDetailsData')
            ->willReturn([
                'id'                   => 1,
                'firstName'            => 'foo',
                'middleName'           => 'bar',
                'surname'              => 'baz',
                'username'             => 'tester1',
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
}