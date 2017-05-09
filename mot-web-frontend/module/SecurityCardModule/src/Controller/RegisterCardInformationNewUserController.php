<?php

namespace Dvsa\Mot\Frontend\SecurityCardModule\Controller;

use Dvsa\Mot\Frontend\SecurityCardModule\Support\TwoFaFeatureToggle;
use Core\Service\LazyMotFrontendAuthorisationService;
use Core\Controller\AbstractDvsaActionController;
use DvsaCommon\Auth\MotIdentityProviderInterface;
use DvsaCommon\Http\HttpStatus;
use Zend\View\Model\ViewModel;
use Zend\Http\Request;
use Zend\Http\Response;

class RegisterCardInformationNewUserController extends AbstractDvsaActionController
{
    const REGISTER_CARD_NEW_USER_INFORMATION_ROUTE = 'security-card-information-new-user';
    const PAGE_TITLE = 'Activate your security card';

    /** @var Request */
    protected $request;

    /** @var Response */
    protected $response;

    /** @var MotIdentityProviderInterface $motIdentityProvider */
    private $motIdentityProvider;

    /** @var LazyMotFrontendAuthorisationService $authorisationService */
    private $authorisationService;

    /** @var TwoFaFeatureToggle */
    private $twoFaFeatureToggle;

    public function __construct(
        Request $request,
        Response $response,
        MotIdentityProviderInterface $motIdentityProvider,
        LazyMotFrontendAuthorisationService $authorisationService,
        TwoFaFeatureToggle $twoFaFeatureToggle
    ) {
        $this->request = $request;
        $this->response = $response;
        $this->motIdentityProvider = $motIdentityProvider;
        $this->authorisationService = $authorisationService;
        $this->twoFaFeatureToggle = $twoFaFeatureToggle;
    }

    public function registerCardInformationNewUserAction()
    {
        $userId = (int) $this->params()->fromRoute('userId', null);
        $userHasActivatedA2FaCard = $this->motIdentityProvider->getIdentity()->isSecondFactorRequired();
        $isDvsa = $this->authorisationService->isDvsa();

        if ($userId == null || !$this->twoFaFeatureToggle->isEnabled()
            || $userId != $this->motIdentityProvider->getIdentity()->getUserId()
            || $isDvsa
            || $userHasActivatedA2FaCard) {
            $this->response->setStatusCode(HttpStatus::HTTP_NOT_FOUND);

            return new ViewModel(array('content' => 'Page not found'));
        }

        $this->layout('layout/layout-govuk.phtml');
        $this->layout()->setVariable('pageTitle', self::PAGE_TITLE);
        $viewModel = new ViewModel();

        return $viewModel->setTemplate('2fa/register-card/register-card-information-new-user');
    }
}
