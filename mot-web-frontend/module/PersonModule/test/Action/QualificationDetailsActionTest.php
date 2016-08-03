<?php

namespace Dvsa\Mot\Frontend\PersonModuleTest\Action;

use Application\Data\ApiPersonalDetails;
use Core\Action\ActionResult;
use Core\Controller\AbstractAuthActionController;
use Dvsa\Mot\Frontend\PersonModule\Action\QualificationDetailsAction;
use Dashboard\Model\PersonalDetails;
use Dvsa\Mot\Frontend\PersonModule\Breadcrumbs\CertificatesBreadcrumbs;
use Dvsa\Mot\Frontend\PersonModule\ViewModel\QualificationDetailsViewModel;
use Dvsa\Mot\Frontend\PersonModule\Security\PersonProfileGuard;
use Dvsa\Mot\Frontend\PersonModule\Security\PersonProfileGuardBuilder;
use Dvsa\Mot\Frontend\PersonModule\View\ContextProvider;
use Dvsa\Mot\Frontend\PersonModule\View\PersonProfileUrlGenerator;
use DvsaClient\Mapper\QualificationDetailsMapper;
use DvsaClient\Mapper\TesterGroupAuthorisationMapper;
use DvsaCommonTest\TestUtils\Auth\AuthorisationServiceMock;
use DvsaCommon\Model\TesterAuthorisation;
use Zend\Session\Container;
use Zend\View\Model\ViewModel;

/**
 * SecurityQuestionController Test.
 */
class QualificationDetailsActionTest extends AbstractAuthActionController
{
    private $personId = 2;

    private $link = 'http://LINK';

    private $requiredPermission = "PERMISSION";

    /** @var QualificationDetailsAction $qualificationDetailsAction */
    private $qualificationDetailsAction;

    /** @var AuthorisationServiceMock $authorisationService */
    private $authorisationService;

    protected function setUp()
    {
        $this->authorisationService = new AuthorisationServiceMock();

        /** @var PersonProfileGuardBuilder $personProfileGuardBuilder */
        $personProfileGuardBuilder = $this
            ->getMockBuilder(PersonProfileGuardBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();

        /** @var PersonProfileGuard $personProfileGuard */
        $personProfileGuard = $this
            ->getMockBuilder(PersonProfileGuard::class)
            ->disableOriginalConstructor()
            ->getMock();

        $personProfileGuard
            ->method('canViewQualificationDetails')
            ->willReturn(true);

        $personProfileGuardBuilder
            ->method('createPersonProfileGuard')
            ->willReturn($personProfileGuard);

        /** @var PersonProfileUrlGenerator $personProfileUrlGenerator */
        $personProfileUrlGenerator = $this
            ->getMockBuilder(PersonProfileUrlGenerator::class)
            ->disableOriginalConstructor()
            ->getMock();

        /** @var ApiPersonalDetails $apiPersonalDetails */
        $apiPersonalDetails = $this
            ->getMockBuilder(ApiPersonalDetails::class)
            ->disableOriginalConstructor()
            ->getMock();

        $apiPersonalDetails
            ->method('getPersonalDetailsData')
            ->willReturn($this->getPersonalDetailsData());

        /** @var ContextProvider $contextProvider */
        $contextProvider = $this
            ->getMockBuilder(ContextProvider::class)
            ->disableOriginalConstructor()
            ->getMock();

        /** @var QualificationDetailsMapper $qualificationDetailsMapper */
        $qualificationDetailsMapper = $this
            ->getMockBuilder(QualificationDetailsMapper::class)
            ->disableOriginalConstructor()
            ->getMock();

        /** @var CertificatesBreadcrumbs $qualificationDetailsBreadcrumbs */
        $qualificationDetailsBreadcrumbs = $this
            ->getMockBuilder(CertificatesBreadcrumbs::class)
            ->disableOriginalConstructor()
            ->getMock();

        $qualificationDetailsBreadcrumbs
            ->method('getBreadcrumbs')
            ->willReturn([]);

        /** @var TesterGroupAuthorisationMapper $testerGroupAuthorisationMapper */
        $testerGroupAuthorisationMapper = $this
            ->getMockBuilder(TesterGroupAuthorisationMapper::class)
            ->disableOriginalConstructor()
            ->getMock();

        /** @var TesterAuthorisation $testerAuthorisation */
        $testerAuthorisation = $this
            ->getMockBuilder(TesterAuthorisation::class)
            ->disableOriginalConstructor()
            ->getMock();

        $testerGroupAuthorisationMapper
            ->method('getAuthorisation')
            ->willReturn($testerAuthorisation);

        $this->qualificationDetailsAction = new QualificationDetailsAction(
            $apiPersonalDetails,
            $this->authorisationService,
            $personProfileUrlGenerator,
            $personProfileGuardBuilder,
            $contextProvider,
            $testerGroupAuthorisationMapper,
            $qualificationDetailsMapper,
            $qualificationDetailsBreadcrumbs
        );
    }

    public function testGetViewFormFirstTimeAction()
    {
        // AND I have permission to do it
        $this->authorisationService->granted($this->requiredPermission);

        // WHEN I view it
        $result = $this->qualificationDetailsAction->execute($this->personId, $this);

        // THEN I'm not redirected anywhere
        $this->assertInstanceOf(ActionResult::class, $result);

        // AND I receive a proper view model
        /** @var QualificationDetailsViewModel $vm */
        $vm = $result->getViewModel();
        $this->assertInstanceOf(QualificationDetailsViewModel::class, $vm);

        // AND title and subtitles are correctly set
        $this->assertEquals("Qualification details", $result->layout()->getPageTitle());
        $this->assertEquals("User profile", $result->layout()->getPageSubTitle());
    }


    /**
     * @param int $personId
     *
     * @return PersonalDetails
     */
    private function getPersonalDetailsData()
    {
        return
            [
                'id' => 1,
                'firstName' => 'firstName',
                'middleName' => 'middleName',
                'surname' => 'surname',
                'dateOfBirth' => 'dateOfBirth',
                'title' => 'title',
                'gender' => 'gender',
                'addressLine1' => 'addressLine1',
                'addressLine2' => 'addressLine2',
                'addressLine3' => 'addressLine3',
                'town' => 'town',
                'postcode' => 'postcode',
                'email' => 'email',
                'phone' => 'phone',
                'drivingLicenceNumber' => 'drivingLicenceNumber',
                'drivingLicenceRegion' => 'drivingLicenceRegion',
                'username' => 'username',
                'positions' => 'positions',
                'roles' => [
                    'system' => [
                        'roles' => [
                            '0' => 'USER',
                        ],
                    ],
                    'organisations' => [],
                    'sites' => [
                        '1' => [
                            'name' => 'name',
                            'number' => 'number',
                            'address' => 'address',
                            'roles' => [
                                '0' => 'TESTER',
                            ],
                        ],
                    ],
                ],
            ];
    }
}
