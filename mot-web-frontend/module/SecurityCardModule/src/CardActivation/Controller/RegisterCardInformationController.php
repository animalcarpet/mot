<?php

namespace Dvsa\Mot\Frontend\SecurityCardModule\CardActivation\Controller;

use Core\Controller\AbstractDvsaActionController;
use Core\Service\LazyMotFrontendAuthorisationService;
use Dvsa\Mot\Frontend\SecurityCardModule\Support\TwoFaFeatureToggle;
use DvsaCommon\Auth\MotIdentityProviderInterface;
use Dvsa\Mot\Frontend\SecurityCardModule\CardActivation\Service\RegisterCardInformationCookieService;
use DvsaCommon\Http\HttpStatus;
use Zend\View\Model\ViewModel;
use Zend\Http\Request;
use Zend\Http\Response;
use Dashboard\Controller\UserHomeController;

class RegisterCardInformationController extends AbstractDvsaActionController
{
    const REGISTER_CARD_INFORMATION_ROUTE = 'security-card-information';
    const PAGE_TITLE = "Changes to your account security";

    /**
     * @var RegisterCardInformationCookieService
     */
    private $cookieService;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var Response
     */
    protected $response;

    /**
     * @var MotIdentityProviderInterface $motIdentityProvider
     */
    private $motIdentityProvider;

    /**
     * @var LazyMotFrontendAuthorisationService $authorisationService
     */
    private $authorisationService;

    /** @var  TwoFaFeatureToggle */
    private $twoFaFeatureToggle;

    public function __construct(
        RegisterCardInformationCookieService $cookieService,
        Request $request,
        Response $response,
        MotIdentityProviderInterface $motIdentityProvider,
        LazyMotFrontendAuthorisationService $authorisationService,
        TwoFaFeatureToggle $twoFaFeatureToggle
    )
    {
        $this->cookieService = $cookieService;
        $this->request = $request;
        $this->response = $response;
        $this->motIdentityProvider = $motIdentityProvider;
        $this->authorisationService = $authorisationService;
        $this->twoFaFeatureToggle = $twoFaFeatureToggle;
    }

    public function registerCardInformationAction()
    {
        $userId = (int) $this->params()->fromRoute('userId', null);

        $userHasActivatedA2FaCard = $this->motIdentityProvider->getIdentity()->isSecondFactorRequired();
        $isDvsa = $this->authorisationService->isDvsa();

        // If the correct user id is not found in the url 404 the page
        if ($userId == null || !$this->twoFaFeatureToggle->isEnabled()
            || $userId != $this->motIdentityProvider->getIdentity()->getUserId()
            || $isDvsa
            || $userHasActivatedA2FaCard) {

            $this->response->setStatusCode(HttpStatus::HTTP_NOT_FOUND);
            return new ViewModel(array('content' => 'Page not found'));
        }

        if ($this->cookieService->validate($this->request)) {
            return $this->redirect()->toRoute(UserHomeController::ROUTE);
        }

        $this->cookieService->addRegisterCardInformationCookie($this->response);

        $this->layout('layout/layout-govuk.phtml');
        $this->layout()->setVariable('pageTitle', self::PAGE_TITLE);

        $viewModel = new ViewModel();
        return $viewModel->setTemplate('2fa/register-card/register-card-information');
    }
}