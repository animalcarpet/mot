<?php

namespace Dvsa\Mot\Frontend\PersonModuleTest\Model;

use Dvsa\Mot\Frontend\PersonModule\Breadcrumbs\CertificatesBreadcrumbs;
use Dvsa\Mot\Frontend\PersonModule\Controller\AddAnnualAssessmentCertificatesController;
use Dvsa\Mot\Frontend\PersonModule\Form\AnnualAssessmentCertificatesForm;
use Dvsa\Mot\Frontend\PersonModule\Model\AnnualAssessmentCertificatesAddProcess;
use Dvsa\Mot\Frontend\PersonModule\Model\FormContext;
use Dvsa\Mot\Frontend\PersonModule\Routes\AnnualAssessmentCertificatesRoutes;
use Dvsa\Mot\Frontend\PersonModule\Security\AnnualAssessmentCertificatesPermissions;
use Dvsa\Mot\Frontend\PersonModule\View\ContextProvider;
use DvsaClient\Mapper\AnnualAssessmentCertificatesMapper;
use DvsaCommon\ApiClient\Person\MotTestingAnnualCertificate\Dto\MotTestingAnnualCertificateDto;
use DvsaCommon\Auth\MotAuthorisationServiceInterface;
use DvsaCommonTest\TestUtils\XMock;
use PHPUnit_Framework_MockObject_MockObject as MockObj;

class AnnualAssessmentCertificatesAddProcessTest extends \PHPUnit_Framework_TestCase
{
    /** @var AnnualAssessmentCertificatesMapper|MockObj */
    private $annualAssessmentCertificatesMapper;
    /** @var CertificatesBreadcrumbs|MockObj */
    private $certificatesBreadcrumbs;
    /** @var ContextProvider|MockObj */
    private $contextProviderMock;
    /** @var AnnualAssessmentCertificatesRoutes|MockObj */
    private $annualAssessmentCertificatesRoutes;
    /** @var AnnualAssessmentCertificatesPermissions|MockObj */
    private $certificatesPermissions;
    /** @var AddAnnualAssessmentCertificatesController|MockObj */
    private $controllerMock;
    /** @var AnnualAssessmentCertificatesAddProcess */
    private $process;
    /** @var MotAuthorisationServiceInterface|MockObj */
    private $motAuthorisationServiceInterface;

    private $badFormData = [
        AnnualAssessmentCertificatesForm::FIELD_SCORE => '0012',
        AnnualAssessmentCertificatesForm::FIELD_CERT_NUMBER => '113',
        AnnualAssessmentCertificatesForm::FIELD_DATE_YEAR => '2011',
        AnnualAssessmentCertificatesForm::FIELD_DATE_MONTH => '2',
        AnnualAssessmentCertificatesForm::FIELD_DATE_DAY => '1',
    ];

    private $formData = [
        AnnualAssessmentCertificatesForm::FIELD_SCORE => '12',
        AnnualAssessmentCertificatesForm::FIELD_CERT_NUMBER => '113',
        AnnualAssessmentCertificatesForm::FIELD_DATE_YEAR => '2011',
        AnnualAssessmentCertificatesForm::FIELD_DATE_MONTH => '2',
        AnnualAssessmentCertificatesForm::FIELD_DATE_DAY => '1',
    ];

    public function setUp()
    {
        $this->annualAssessmentCertificatesMapper = XMock::of(AnnualAssessmentCertificatesMapper::class);
        $this->annualAssessmentCertificatesRoutes = Xmock::of(AnnualAssessmentCertificatesRoutes::class);
        $this->contextProviderMock = Xmock::of(ContextProvider::class);
        $this->certificatesBreadcrumbs = Xmock::of(CertificatesBreadcrumbs::class);
        $this->certificatesPermissions = XMock::of(AnnualAssessmentCertificatesPermissions::class);

        $this->process = new AnnualAssessmentCertificatesAddProcess(
            $this->contextProviderMock,
            $this->annualAssessmentCertificatesRoutes,
            $this->annualAssessmentCertificatesMapper,
            $this->certificatesBreadcrumbs,
            $this->certificatesPermissions
            );

        $this->controllerMock = Xmock::of(AddAnnualAssessmentCertificatesController::class);

        $context = (new FormContext(1, 1, 'A', $this->controllerMock));

        $this->process->setContext($context);

        $this->motAuthorisationServiceInterface = XMock::of(MotAuthorisationServiceInterface::class);
    }

    /**
     * @dataProvider dataProviderTestIsAuthorisedToCreateAnnualAssessmentCertificates
     *
     * @param $result
     */
    public function testIsAuthorisedToCreateAnnualAssessmentCertificates($result)
    {
        $this->certificatesPermissions->method('isGrantedCreate')
            ->willReturn($result);

        $this->assertSame($result, $this->process->isAuthorised($this->motAuthorisationServiceInterface));
    }

    public function dataProviderTestIsAuthorisedToCreateAnnualAssessmentCertificates()
    {
        return [
            [true],
            [false],
        ];
    }

    /**
     * @dataProvider dataProviderTestUpdate
     *
     * @param $formData
     * @param $expected
     */
    public function testUpdate($formData, $expected)
    {
        $this->annualAssessmentCertificatesMapper
            ->method('createAnnualAssessmentCertificate')
            ->willReturn($expected);

        $this->annualAssessmentCertificatesMapper
            ->method('mapFormDataToDto')
            ->willReturn(new MotTestingAnnualCertificateDto());

        $actual = $this->process->update($formData);
        $this->assertSame($expected, $actual);
    }

    public function dataProviderTestUpdate()
    {
        return [
            [
                'formData' => $this->formData,
                'expected' => true,
            ],
            [
                'formData' => $this->formData,
                'expected' => false,
            ],
        ];
    }

    /**
     * @dataProvider dataProviderTestTransformFormIntoGdsTable
     *
     * @param $input
     * @param $expected
     */
    public function testTransformFormIntoGdsTable($input, $expected)
    {
        $result = $this->process->transformFormIntoGdsTable($input);

        $certificateNumber = $result->getRow(0)->getValue();
        $scoreAchieved = $result->getRow(2)->getValue();

        $this->assertEquals($certificateNumber->getContent(), $expected[AnnualAssessmentCertificatesForm::FIELD_CERT_NUMBER]);
        $this->assertEquals($scoreAchieved->getContent(), ($expected[AnnualAssessmentCertificatesForm::FIELD_SCORE].'%'));
    }

    public function dataProviderTestTransformFormIntoGdsTable()
    {
        return [
            [
                'input' => $this->formData,
                'expected' => $this->formData,
            ],
            [
                'input' => $this->badFormData,
                'expected' => $this->formData,
            ],
        ];
    }

    public function testIfFormIsNotPrepopulated()
    {
        $this->assertEmpty($this->process->getPrePopulatedData());
    }
}
