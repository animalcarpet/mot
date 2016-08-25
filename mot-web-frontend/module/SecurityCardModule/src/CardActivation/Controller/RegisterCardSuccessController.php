<?php

namespace Dvsa\Mot\Frontend\SecurityCardModule\CardActivation\Controller;

use Core\Controller\AbstractDvsaActionController;
use Dvsa\Mot\Frontend\SecurityCardModule\CardActivation\Action\RegisterCardGetAction;
use Dvsa\Mot\Frontend\SecurityCardModule\CardActivation\Action\RegisterCardPostAction;
use Dvsa\Mot\Frontend\SecurityCardModule\CardActivation\Action\RegisterCardSuccessAction;
use Dvsa\Mot\Frontend\SecurityCardModule\CardValidation\Service\RegisteredCardService;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use DvsaFeature\FeatureToggles;
use Zend\Http\Request;
use Zend\Http\Response;
use Zend\Mvc\MvcEvent;
use Zend\View\Model\ViewModel;


class RegisterCardSuccessController extends AbstractDvsaActionController implements AutoWireableInterface
{
    /** @var RegisterCardSuccessAction */
    private $action;

    public function __construct(RegisterCardSuccessAction $action)
    {
        $this->action = $action;
    }

    /**
     * Redirect to Success Page
     */
    public function successAction()
    {
        return $this->applyActionResult($this->action->execute($this->getRequest()));
    }
}
