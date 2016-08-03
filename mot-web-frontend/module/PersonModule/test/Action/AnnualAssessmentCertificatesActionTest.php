<?php

namespace Dvsa\Mot\Frontend\PersonModuleTest\Action;

use Core\Action\ActionResult;
use Core\Controller\AbstractAuthActionController;
use Dvsa\Mot\Frontend\PersonModule\Action\AnnualAssessmentCertificatesAction;
use Dvsa\Mot\Frontend\PersonModule\Breadcrumbs\PersonProfileBreadcrumbs;
use Dvsa\Mot\Frontend\PersonModule\Model\FormContext;
use Dvsa\Mot\Frontend\PersonModule\Routes\AnnualAssessmentCertificatesRoutes;
use Dvsa\Mot\Frontend\PersonModule\Security\AnnualAssessmentCertificatesPermissions;
use Dvsa\Mot\Frontend\PersonModule\View\PersonProfileUrlGenerator;
use Dvsa\Mot\Frontend\PersonModule\ViewModel\AnnualAssessmentCertificates\AnnualAssessmentCertificatesGroupViewModel;
use Dvsa\Mot\Frontend\PersonModule\ViewModel\AnnualAssessmentCertificates\AnnualAssessmentCertificatesViewModel;
use DvsaClient\Mapper\AnnualAssessmentCertificatesMapper;
use DvsaCommonTest\TestUtils\XMock;
use PHPUnit_Framework_TestCase;
use Zend\Session\Container;
use Zend\View\Model\ViewModel;
use PHPUnit_Framework_MockObject_MockObject as MockObj;


abstract class AnnualAssessmentCertificatesActionTest extends AbstractAuthActionController
{
    /** @var AnnualAssessmentCertificatesAction $annualAssessmentCertificatesAction */
    private $annualAssessmentCertificatesAction;

    protected function setUp()
    {
        /** @var PersonProfileUrlGenerator $personProfileUrlGenerator */
        $personProfileUrlGenerator = XMock::of(PersonProfileUrlGenerator::class);

        /** @var PersonProfileBreadcrumbs|MockObj $personProfileBreadcrumbs */
        $personProfileBreadcrumbs = XMock::of(PersonProfileBreadcrumbs::class);

        $personProfileBreadcrumbs
            ->method('getBreadcrumbs')
            ->willReturn([]);

        /** @var AnnualAssessmentCertificatesMapper $annualAssessmentCertificatesMapper */
        $annualAssessmentCertificatesMapper = XMock::of(AnnualAssessmentCertificatesMapper::class);

        /** @var AnnualAssessmentCertificatesRoutes $annualAssessmentCertificatesRoutes */
        $annualAssessmentCertificatesRoutes = XMock::of(AnnualAssessmentCertificatesRoutes::class);

        /** @var AnnualAssessmentCertificatesPermissions $certificatesPermissions */
        $certificatesPermissions = XMock::of(AnnualAssessmentCertificatesPermissions::class);

        $this->annualAssessmentCertificatesAction = new AnnualAssessmentCertificatesAction(
            $personProfileBreadcrumbs,
            $personProfileUrlGenerator,
            $annualAssessmentCertificatesMapper,
            $annualAssessmentCertificatesRoutes,
            $certificatesPermissions
        );
    }

    public function testActionResult()
    {
        $formContext = new FormContext('1', '1', 'A', $this);

        //WHEN I view it
        $result = $this->annualAssessmentCertificatesAction->execute($formContext, $this);

        // THEN I'm not redirected anywhere
        $this->assertInstanceOf(ActionResult::class, $result);

        //AND fields are generated correct
        $this->assertInstanceOf(AnnualAssessmentCertificatesViewModel::class, $result->getViewModel());

        /** @var AnnualAssessmentCertificatesViewModel $vm */
        $vm = $result->getViewModel();
        $this->assertEquals($vm->getPageTitle(), $result->layout()->getPageTitle());
        $this->assertEquals($vm->getPageSubtitle(), $result->layout()->getPageSubTitle());
        $this->assertEquals($vm->getTemplate(), $result->layout()->getTemplate());
        $this->assertInstanceOf(AnnualAssessmentCertificatesGroupViewModel::class, $vm->getAnnualAssessmentCertificatesGroupAViewModel());
        $this->assertInstanceOf(AnnualAssessmentCertificatesGroupViewModel::class, $vm->getAnnualAssessmentCertificatesGroupBViewModel());
    }
}
