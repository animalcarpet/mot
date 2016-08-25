<?php

namespace Dvsa\Mot\Frontend\SecurityCardModule\CardOrder\Controller;

use Core\Controller\AbstractDvsaActionController;
use Dvsa\Mot\Frontend\AuthenticationModule\Model\Identity;
use Dvsa\Mot\Frontend\SecurityCardModule\CardOrder\Service\OrderNewSecurityCardSessionService;
use Zend\Http\Request;
use Zend\Mvc\MvcEvent;
use Zend\View\Model\ViewModel;

class CardOrderConfirmationController extends AbstractDvsaActionController
{
    /**
     * @var OrderNewSecurityCardSessionService
     */
    protected $session;

    private $identity;

    public function __construct(
        OrderNewSecurityCardSessionService $securityCardSessionService,
        Identity $identity
    )
    {
        $this->session = $securityCardSessionService;
        $this->identity = $identity;
    }

    public function indexAction()
    {
        $userId = $this->params()->fromRoute('userId', $this->identity->getUserId());
        if (false === $this->checkValidSession()) {
            $this->redirectToStart($userId);
        }

        $this->buildBreadcrumbs();

        // As this is the last page of the journey clear the session
        $this->session->clearByGuid($userId);
        return (new ViewModel())->setTemplate('2fa/card-order/confirmation');
    }

    /**
     * If there is no valid session, we should go to the journey start.
     *
     * @return \Zend\Http\Response
     */
    protected function checkValidSession()
    {
        $values = $this->session->toArray();

        return !(is_array($values) && count($values) === 0);
    }

    protected function redirectToStart($userId)
    {
        return $this->redirect()->toRoute('security-card-order/new', ['userId' => $userId]);
    }

    protected function buildBreadcrumbs()
    {
        $this->getBreadcrumbBuilder()
            ->simple('Your profile', 'newProfile')
            ->simple('Order a security card')
            ->build();
    }
}
