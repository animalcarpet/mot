<?php

namespace Dvsa\Mot\Frontend\SecurityCardModule\CardActivation\Controller;

use Application\Data\ApiPersonalDetails;
use Application\Service\CatalogService;
use Core\Controller\AbstractAuthActionController;
use Dashboard\Controller\UserHomeController;
use Dvsa\Mot\Frontend\PersonModule\View\ContextProvider;
use Dvsa\Mot\Frontend\SecurityCardModule\Security\SecurityCardGuard;
use Dvsa\Mot\Frontend\SecurityCardModule\Service\SecurityCardService;
use DvsaCommon\Auth\MotIdentityProviderInterface;
use DvsaCommon\Date\DateTimeDisplayFormat;
use Zend\View\Model\ViewModel;

class AlreadyHasRegisteredCardController extends AbstractAuthActionController
{
    const PAGE_TITLE = 'Activate your security card';
    const ROUTE = 'register-card/already-has-card';
    const PAGE_TEMPLATE = '2fa/register-card/already-has-card';

    /** @var ApiPersonalDetails */
    private $personalDetailsService;

    /** @var CatalogService */
    private $catalogService;

    /** @var ContextProvider */
    private $contextProvider;

    /** @var SecurityCardService */
    private $securityCardService;

    /** @var MotIdentityProviderInterface */
    private $identityProvider;

    /** @var SecurityCardGuard */
    private $securityCardGuard;

    /**
     * AlreadyHasRegisteredCardController constructor.
     *
     * @param SecurityCardService          $securityCardService
     * @param ContextProvider              $contextProvider
     * @param ApiPersonalDetails           $personalDetailsService
     * @param CatalogService               $catalogService
     * @param MotIdentityProviderInterface $identityProvider
     * @param SecurityCardGuard            $securityCardGuard
     */
    public function __construct(
        SecurityCardService $securityCardService,
        ContextProvider $contextProvider,
        ApiPersonalDetails $personalDetailsService,
        CatalogService $catalogService,
        MotIdentityProviderInterface $identityProvider,
        SecurityCardGuard $securityCardGuard
    ) {
        $this->securityCardService = $securityCardService;
        $this->contextProvider = $contextProvider;
        $this->personalDetailsService = $personalDetailsService;
        $this->catalogService = $catalogService;
        $this->identityProvider = $identityProvider;
        $this->securityCardGuard = $securityCardGuard;
    }

    public function indexAction()
    {
        $this->layout('layout/layout-govuk.phtml');
        $this->setHeadTitle('You have already activated a security card');

        $securityCard = null;
        $identity = $this->identityProvider->getIdentity();
        if ($this->securityCardGuard->hasActiveTwoFaCard($identity)) {
            $securityCard = $this->securityCardService->getSecurityCardForUser($identity->getUsername());
        }

        if ($securityCard === null) {
            return $this->redirect()->toRoute(UserHomeController::ROUTE);
        }

        $dateFormatter = new DateTimeDisplayFormat();
        $securityCardDate = $dateFormatter::textDate($securityCard->getActivationDate());

        $this->layout()->setVariable('pageTitle', self::PAGE_TITLE);

        return $this->createViewModel(self::PAGE_TEMPLATE, [
            'securityCard' => $securityCard,
            'securityCardDate' => $securityCardDate,
        ]);
    }

    private function createViewModel($template, array $variables)
    {
        $viewModel = new ViewModel();
        $viewModel->setTemplate($template);
        $viewModel->setVariables($variables);

        return $viewModel;
    }
}
