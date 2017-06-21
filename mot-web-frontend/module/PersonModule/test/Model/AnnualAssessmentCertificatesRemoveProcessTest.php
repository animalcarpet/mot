<?php

namespace PersonModule\test\Model;

use Core\ViewModel\Gds\Table\GdsTable;
use Dvsa\Mot\Frontend\PersonModule\Breadcrumbs\CertificatesBreadcrumbs;
use Dvsa\Mot\Frontend\PersonModule\Controller\RemoveAnnualAssessmentCertificatesController;
use Dvsa\Mot\Frontend\PersonModule\Model\AnnualAssessmentCertificatesFormContext;
use Dvsa\Mot\Frontend\PersonModule\Routes\AnnualAssessmentCertificatesRoutes;
use Dvsa\Mot\Frontend\PersonModule\Security\AnnualAssessmentCertificatesPermissions;
use Dvsa\Mot\Frontend\PersonModule\Model\AnnualAssessmentCertificatesRemoveProcess;
use Dvsa\Mot\Frontend\PersonModule\View\ContextProvider;
use DvsaCommon\ApiClient\Person\MotTestingAnnualCertificate\Dto\MotTestingAnnualCertificateDto;
use DvsaCommon\ApiClient\Person\MotTestingAnnualCertificate\MotTestingAnnualCertificateApiResource;
use DvsaCommon\Enum\VehicleClassGroupCode;
use DvsaCommonTest\TestUtils\Auth\AuthorisationServiceMock;
use DvsaCommonTest\TestUtils\XMock;
use Zend\Form\Form;
use Zend\Mvc\Controller\Plugin\Params;

class AnnualAssessmentCertificatesRemoveProcessTest extends \PHPUnit_Framework_TestCase
{
    const TARGET_PERSON_ID = 1;
    const LOGGED_PERSON_ID = 2;
    const GROUP = VehicleClassGroupCode::BIKES;
    const CERTIFICATE_ID = 3;
    const CERTIFICATE_NUMBER = 'CERTIFICATE NUMBER';
    const DATE_AWARDED = '10-10-2010';
    const EXPECTED_DATE_AWARDED = '10 October 2010';
    const SCORE = '90';
    const EXPECTED_SCORE = '90%';
    const BACK_ROUTE = '/this-is-back-route';

    /** @var ContextProvider|\PHPUnit_Framework_MockObject_MockObject */
    private $contextProvider;
    /** @var AnnualAssessmentCertificatesRoutes|\PHPUnit_Framework_MockObject_MockObject */
    private $routes;
    /** @var MotTestingAnnualCertificateApiResource|\PHPUnit_Framework_MockObject_MockObject */
    private $apiResource;
    /** @var CertificatesBreadcrumbs|\PHPUnit_Framework_MockObject_MockObject */
    private $breadcrumbs;
    /** @var AnnualAssessmentCertificatesPermissions|\PHPUnit_Framework_MockObject_MockObject */
    private $permissions;
    /** @var AnnualAssessmentCertificatesRemoveProcess */
    private $sut;

    public function setUp()
    {
        $this->contextProvider = XMock::of(ContextProvider::class);
        $this->routes = XMock::of(AnnualAssessmentCertificatesRoutes::class);
        $this->apiResource = XMock::of(MotTestingAnnualCertificateApiResource::class);
        $this->breadcrumbs = XMock::of(CertificatesBreadcrumbs::class);
        $this->permissions = XMock::of(AnnualAssessmentCertificatesPermissions::class);

        $this->sut = new AnnualAssessmentCertificatesRemoveProcess(
            $this->contextProvider,
            $this->routes,
            $this->apiResource,
            $this->breadcrumbs,
            $this->permissions
        );
        $this->sut->setContext($this->createContext());
    }

    public function testUpdateCallsApi()
    {
        $this->apiResource
            ->expects($this->once())
            ->method('remove')
            ->with(self::TARGET_PERSON_ID, self::GROUP, self::CERTIFICATE_ID);

        $this->sut->update($this->createFormData());
    }

    public function testGetPrePopulatedData()
    {
        $formData = $this->sut->getPrePopulatedData();

        $this->assertEquals([], $formData);
    }

    public function testGetBreadcrumbs()
    {
        $breadcrumbs = ['bread' => 'crumb'];

        $this->breadcrumbs
            ->expects($this->once())
            ->method('getBreadcrumbsForAnnualAssessmentCertificate')
            ->willReturn($breadcrumbs);

        $this->assertSame($breadcrumbs, $this->sut->getBreadcrumbs($this->createAuthorisationService()));
    }

    public function testBuildEditStepViewModel()
    {
        $this->routes
            ->expects($this->once())
            ->method('getRoute')
            ->willReturn(self::BACK_ROUTE);

        $this->apiResource
            ->expects($this->once())
            ->method('get')
            ->willReturn($this->createDto());

        $this->sut->getPrePopulatedData();

        $viewModel = $this->sut->buildEditStepViewModel($this->createForm());

        /** @var GdsTable $table */
        $table = $viewModel->getTable();
        $this->assertEquals(self::CERTIFICATE_NUMBER, $table->getRow(0)->getValue()->getContent());
        $this->assertEquals(self::EXPECTED_DATE_AWARDED, $table->getRow(1)->getValue()->getContent());
        $this->assertEquals(self::EXPECTED_SCORE, $table->getRow(2)->getValue()->getContent());

        $this->assertEquals('Remove your assessment certificate', $viewModel->getEditStepPageTitle());

        $this->assertEquals('Your profile', $viewModel->getPageSubTitle());

        $this->assertEquals('Remove certificate', $viewModel->getSubmitButtonText());

        $this->assertEquals(self::BACK_ROUTE, $viewModel->getBackRoute());

        $this->assertEquals([
            'id' => self::TARGET_PERSON_ID,
            'group' => self::GROUP,
        ], $viewModel->getBackRouteParams());
    }

    private function createContext()
    {
        return new AnnualAssessmentCertificatesFormContext(
            self::TARGET_PERSON_ID,
            self::LOGGED_PERSON_ID,
            self::GROUP,
            self::CERTIFICATE_ID,
            $this->createController()
        );
    }

    private function createController()
    {
        $paramsPlugin = XMock::of(Params::class);
        $paramsPlugin
            ->method("fromRoute")
            ->willReturn([]);

        $controller = XMock::of(RemoveAnnualAssessmentCertificatesController::class, ["params"]);
        $controller
            ->method("params")
            ->willReturn($paramsPlugin);

        return $controller;
    }

    private function createFormData()
    {
        return [
            'cert-number' => self::CERTIFICATE_NUMBER,
            'date-awarded' => self::EXPECTED_DATE_AWARDED,
            'score' => self::EXPECTED_SCORE,
        ];
    }

    private function createDto()
    {
        $dto = new MotTestingAnnualCertificateDto();
        $dto->setCertificateNumber(self::CERTIFICATE_NUMBER)
            ->setExamDate(new \DateTime(self::DATE_AWARDED))
            ->setId(self::CERTIFICATE_ID)
            ->setScore(self::SCORE);

        return $dto;
    }

    private function createForm()
    {
        return new Form();
    }

    private function createAuthorisationService()
    {
        return new AuthorisationServiceMock();
    }
}
