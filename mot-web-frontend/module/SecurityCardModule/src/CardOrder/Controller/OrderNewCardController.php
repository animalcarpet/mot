<?php

namespace Dvsa\Mot\Frontend\SecurityCardModule\CardOrder\Controller;

use Core\Controller\AbstractDvsaActionController;
use Dvsa\Mot\Frontend\SecurityCardModule\CardOrder\Action\CardOrderNewAction;
use Zend\Http\Request;
use Dvsa\Mot\Frontend\AuthenticationModule\Model\Identity;

class OrderNewCardController extends AbstractDvsaActionController
{
    /**
     * @var CardOrderNewAction
     */
    private $action;

    private $identity;

    public function __construct(CardOrderNewAction $action, Identity $identity)
    {
        $this->action = $action;
        $this->identity = $identity;
    }

    public function indexAction()
    {
        $this->buildBreadcrumbs();

        $userId = $this->params()->fromRoute('userId', $this->identity->getUserId());

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
