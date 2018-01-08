<?php

namespace Application\Controller;

use Application\Service\LoggedInUserManager;
use Core\Controller\AbstractAuthActionController;
use Core\Service\MotFrontendAuthorisationServiceInterface;
use Dvsa\Mot\Frontend\AuthenticationModule\Model\Identity;
use DvsaCommon\Constants\FeatureToggle;
use DvsaCommon\Constants\JasperContingencyCertificateName;
use DvsaCommon\Constants\Role;
use DvsaCommon\HttpRestJson\Exception\RestApplicationException;
use DvsaCommon\UrlBuilder\ReportUrlBuilder;
use DvsaCommon\UrlBuilder\UrlBuilder;
use DvsaMotTest\Service\OverdueSpecialNoticeAssertion;
use DvsaCommon\HttpRestJson\Client;
use Zend\Http\Response;
use Zend\View\Model\ViewModel;

/**
 * Class FormsController.
 */
class FormsController extends AbstractAuthActionController
{
    const PHP_CONTENT_HEADER = 'Content-type: text/html; charset=UTF-8';

    /** @var LoggedInUserManager $loggedInUserManager */
    private $loggedInUserManager;

    /** @var MotFrontendAuthorisationServiceInterface $authorisationService */
    private $authorisationService;

    /** @var Client $client */
    private $client;

    public function __construct(
        LoggedInUserManager $loggedInUserManager,
        MotFrontendAuthorisationServiceInterface $authorisationService,
        Client $client
    ) {
        $this->loggedInUserManager = $loggedInUserManager;
        $this->authorisationService = $authorisationService;
        $this->client = $client;
    }

    public function indexAction()
    {
        // VM-4217: Role based solution done for this sprint. A permissions based solution
        // will need to be implemented when Rbca et al. is completely stable and ready.

        if ($this->authorisationService->isTester()) {
            $tester = $this->loggedInUserManager->getTesterData();
            $authorisationsForTestingMot = (!is_null($tester['authorisationsForTestingMot'])) ? $tester['authorisationsForTestingMot'] : [];

            $url = (new UrlBuilder())->specialNoticeOverdue()->toString();
            $overdueSpecialNotices = $this->client->get($url)['data'];

            $overdueSpecialNotices = new OverdueSpecialNoticeAssertion($overdueSpecialNotices, $authorisationsForTestingMot);
            $overdueSpecialNotices->assertPerformTest();
        }

        $userDetails = $this->getUserDisplayDetails();
        $view = new ViewModel(
            [
                'userDetails' => $userDetails,
                'isVE' => $this->authorisationService->hasRole(Role::VEHICLE_EXAMINER),
            ]
        );

        $this->layout('layout/layout-govuk.phtml');
        $this->layout()->setVariable('breadcrumbs', ['breadcrumbs' => ['MOT forms' => '']]);
        $this->layout()->setVariable('pageTitle', 'MOT forms');
        $this->layout()->setVariable('pageLede', 'Download and print blank copies of common forms.');

        $view->setTemplate('application/index/forms.phtml');

        return $view;
    }

    public function contingencyPassCertificateAction()
    {
       if ($this->isFeatureEnabled(FeatureToggle::EU_ROADWORTHINESS)) {
           return $this->fetchReport(JasperContingencyCertificateName::EU_CT20);
       }
        return $this->fetchReport(JasperContingencyCertificateName::CT20);
    }

    public function contingencyFailCertificateAction()
    {
        if ($this->isFeatureEnabled(FeatureToggle::EU_ROADWORTHINESS)) {
            return $this->fetchReport(JasperContingencyCertificateName::EU_CT30);
        }
        return $this->fetchReport(JasperContingencyCertificateName::CT30);
    }

    public function contingencyAdvisoryCertificateAction()
    {
        return $this->fetchReport(JasperContingencyCertificateName::CT32);
    }

    protected function fetchReport($name)
    {
        /** @var Identity $user */
        $user = $this->getUserDisplayDetails()['user'];
        $vts = $user->getCurrentVts();

        if (is_null($vts)) {
            // We don't know where we are, ask first...
            $event = $this->getEvent();
            $routeMatch = $event->getRouteMatch();
            $route = $routeMatch->getMatchedRouteName();
            $container = $this->getServiceLocator()->get('LocationSelectContainerHelper');
            $container->persistConfig(['route' => $route, 'params' => $routeMatch->getParams()]);

            return $this->redirect()->toRoute('location-select');
        } else {
            // We have a location, we can ask for the certificate...
            try {
                $certificateUrl = ReportUrlBuilder::printContingencyCertificate($name)
                    ->queryParams(
                        [
                            'testStation' => $vts->getSiteNumber(),
                            'inspAuthority' => $this->formatAddress($vts),
                        ]
                    );

                $result = $this->client->getPdf($certificateUrl);
            } catch (RestApplicationException $re) {
                $this->addErrorMessages($re->getDisplayMessages());
                throw $re;
            } catch (\Exception $e) {
                $this->addErrorMessages($e->getMessage());
                throw $e;
            }

            $response = new Response();
            $response->setContent($result);
            $response->getHeaders()->addHeaderLine('Content-Type', 'application/pdf');

            return $response;
        }
    }

    /**
     * @param \Dvsa\Mot\Frontend\AuthenticationModule\Model\VehicleTestingStation $vts
     *
     * @return string
     */
    protected function formatAddress($vts)
    {
        return $vts->getName().PHP_EOL.preg_replace("/,\s*/", PHP_EOL, $vts->getAddress());
    }
}
