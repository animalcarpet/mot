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

class NewUserOrderCardController extends AbstractDvsaActionController
{
    const ORDER_CARD_NEW_USER_ROUTE = 'order-card-new-user';
    const PAGE_TITLE = 'Order your security card';

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

    public function orderCardNewUserAction()
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

        return $viewModel->setTemplate('2fa/register-card/order-card-new-user');
    }
}
