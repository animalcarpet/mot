<?php
namespace Dvsa\Mot\Frontend\PersonModule\Service;

use DvsaCommon\ApiClient\Person\MotTestingCertificate\Dto\MotTestingCertificateDto;
use DvsaCommon\Date\DateTimeDisplayFormat;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use DvsaClient\Mapper\QualificationDetailsMapper;
use DvsaCommon\Auth\MotAuthorisationServiceInterface;
use Dvsa\Mot\Frontend\PersonModule\Breadcrumbs\CertificatesBreadcrumbs;
use Core\ViewModel\Gds\Table\GdsTable;
use Core\Action\ViewActionResult;
use Zend\View\Model\ViewModel;
use Core\Action\RedirectToRoute;
use Application\Data\ApiPersonalDetails;
use Dashboard\Model\PersonalDetails;
use Dvsa\Mot\Frontend\PersonModule\View\ContextProvider;
use Dvsa\Mot\Frontend\PersonModule\Security\PersonProfileGuardBuilder;
use DvsaCommon\Exception\UnauthorisedException;

class RemoveCertificateDetailsService implements AutoWireableInterface
{
    const PAGE_TITLE = "Remove certificate";
    const PAGE_SUBTITLE_YOUR_PROFILE = "Your profile";
    const PAGE_SUBTITLE_USER_PROFILE = "User profile";
    const PAGE_LEDE = "Confirm that you want to remove the Group %s certificate";

    const SUCCESS_MESSAGE = "Group %s certificate removed successfully. Qualification status has been changed to Not applied.";
    const ERROR_MESSAGE = "Group %s certificate has not been removed. Please try again";

    const ACCESS_DENIED = "You have no access to see this page.";

    private $authorisationService;
    private $qualificationDetailsMapper;
    private $personalDetailsService;
    private $contextProvider;
    private $personProfileGuardBuilder;
    private $breadcrumbs = [];

    public function __construct(
        MotAuthorisationServiceInterface $authorisationService,
        QualificationDetailsMapper $qualificationDetailsMapper,
        ApiPersonalDetails $personalDetailsService,
        ContextProvider $contextProvider,
        PersonProfileGuardBuilder $personProfileGuardBuilder
    ) {
        $this->authorisationService = $authorisationService;
        $this->qualificationDetailsMapper = $qualificationDetailsMapper;
        $this->personalDetailsService = $personalDetailsService;
        $this->contextProvider = $contextProvider;
        $this->personProfileGuardBuilder = $personProfileGuardBuilder;
    }

    public function setBreadcrumbs(array $breadcrumbs)
    {
        $this->breadcrumbs = $breadcrumbs;
        return $this;
    }

    public function process($personId, $group, $backUrl, $isPost = false)
    {
        $this->assertGranted($personId, $group);

        if ($isPost) {
            return $this->executePost($personId, $group, $backUrl);
        } else {
            return $this->executeGet($personId, $group, $backUrl);
        }
    }

    private function executePost($personId, $group, $backUrl)
    {
        try {
            $this
                ->qualificationDetailsMapper
                ->removeQualificationDetails($personId, $group);


            $rtr = new RedirectToRoute($backUrl, ["id" => $personId, "group" => $group]);
            $rtr->addSuccessMessage(sprintf(self::SUCCESS_MESSAGE, strtoupper($group)));

            return $rtr;
        } catch (\Exception $e) {
            return $this->buildActionResult($personId, $group, $backUrl, [sprintf(self::ERROR_MESSAGE, strtoupper($group))]);
        }
    }

    private function executeGet($personId, $group, $backUrl)
    {
        return $this->buildActionResult($personId, $group, $backUrl);
    }

    private function buildActionResult($personId, $group, $backUrl, array $errors = [])
    {
        $vm = new ViewModel();
        $vm
            ->setVariable("table", $this->getGdsTable($personId, $group))
            ->setVariable("backUrl", $backUrl)
            ->setVariable('personId', $personId)
        ;

        $actionResult = new ViewActionResult();
        $actionResult->setViewModel($vm);
        $actionResult->addErrorMessages($errors);

        $actionResult->setTemplate('qualification-details/remove.phtml');

        $actionResult->layout()->setPageTitle(self::PAGE_TITLE);
        $actionResult->layout()->setPageSubTitle(
            $this->contextProvider->getContext() == ContextProvider::YOUR_PROFILE_CONTEXT ? static::PAGE_SUBTITLE_YOUR_PROFILE: static::PAGE_SUBTITLE_USER_PROFILE
        );
        $actionResult->layout()->setPageLede(sprintf(self::PAGE_LEDE, strtoupper($group)));

        $actionResult->layout()->setTemplate('layout/layout-govuk.phtml');
        $actionResult->layout()->setBreadcrumbs($this->breadcrumbs);

        return $actionResult;
    }

    private function getGdsTable($personId, $group)
    {
        $certificate = $this->retrieveCertificate($personId, $group);

        $table = new GdsTable();
        $table->newRow()->setLabel('Certificate number')->setValue($certificate->getCertificateNumber());

        $date = new \DateTime($certificate->getDateOfQualification());
        $table->newRow()->setLabel('Date awarded')->setValue($date->format(DateTimeDisplayFormat::FORMAT_DATE));

        return $table;
    }

    /**
     * @param $personId
     * @param $group
     * @return MotTestingCertificateDto
     */
    private function retrieveCertificate($personId, $group)
    {
        return $this
            ->qualificationDetailsMapper
            ->getQualificationDetails($personId, $group);
    }

    private function assertGranted($personId, $group)
    {
        $personalDetailsData = $this->personalDetailsService->getPersonalDetailsData($personId);
        $personalDetails = new PersonalDetails($personalDetailsData);
        $context = $this->contextProvider->getContext();
        $personProfileGuard = $this->personProfileGuardBuilder->createPersonProfileGuard(
            $personalDetails,
            $context
        );

        $cert = $this->retrieveCertificate($personId, $group);

        if (!$personProfileGuard->canRemoveQualificationDetails(strtoupper($group)) || empty($cert)) {
            throw new UnauthorisedException(self::ACCESS_DENIED);
        }
    }
}
