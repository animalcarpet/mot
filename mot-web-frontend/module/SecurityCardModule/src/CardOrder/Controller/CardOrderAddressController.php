<?php

namespace Dvsa\Mot\Frontend\SecurityCardModule\CardOrder\Controller;

use Core\Controller\AbstractDvsaActionController;
use Dvsa\Mot\Frontend\SecurityCardModule\CardOrder\Action\CardOrderAddressAction;
use Zend\Http\Request;
use Dvsa\Mot\Frontend\SecurityCardModule\CardOrder\Service\OrderSecurityCardAddressService;
use Dvsa\Mot\Frontend\AuthenticationModule\Model\Identity;

class CardOrderAddressController extends AbstractDvsaActionController
{
    /**
     * @var OrderSecurityCardAddressService
     */
    private $orderSecurityCardAddressService;

    /**
     * @var CardOrderAddressAction
     */
    private $action;

    /**
     * CardOrderAddressController constructor.
     *
     * @param OrderSecurityCardAddressService $orderSecurityCardAddressService
     * @param CardOrderAddressAction          $action
     */
    public function __construct(
        OrderSecurityCardAddressService $orderSecurityCardAddressService,
        CardOrderAddressAction $action,
        Identity $identity
    ) {
        $this->orderSecurityCardAddressService = $orderSecurityCardAddressService;
        $this->action = $action;
        $this->identity = $identity;
    }

    public function indexAction()
    {
        $this->buildBreadcrumbs();
        $userId = $this->params()->fromRoute('userId', $this->identity->getUserId());
        $this->orderSecurityCardAddressService->getSecurityCardOrderAddresses($userId);
        $result = $this->action->execute($this->request, $userId);

        return $this->applyActionResult($result);
    }

    private function buildBreadcrumbs()
    {
        $this->getBreadcrumbBuilder()
            ->simple('Your profile', 'newProfile')
            ->simple('Order a security card')
            ->build();
    }
}
