<?php

namespace Dvsa\Mot\Frontend\SecurityCardModule\CardActivation\Controller;

use Core\Controller\AbstractDvsaActionController;
use Dvsa\Mot\Frontend\SecurityCardModule\CardActivation\Action\RegisterCardGetAction;
use Dvsa\Mot\Frontend\SecurityCardModule\CardActivation\Action\RegisterCardPostAction;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use DvsaFeature\FeatureToggles;
use Zend\Http\Request;
use Zend\Http\Response;
use Zend\Mvc\MvcEvent;
use Zend\View\Model\ViewModel;


class RegisterCardController extends AbstractDvsaActionController implements AutoWireableInterface
{
    private $getAction;
    private $postAction;

    /**
     * @param RegisterCardGetAction $getAction
     * @param RegisterCardPostAction $postAction
     */
    public function __construct(
        RegisterCardGetAction $getAction,
        RegisterCardPostAction $postAction
    ) {
        $this->getAction = $getAction;
        $this->postAction = $postAction;
    }

    /**
     * @return ViewModel
     */
    public function registerAction()
    {
        if($this->request->isPost()) {
            $result = $this->postAction->execute($this->request);
        } else {
            $result = $this->getAction->execute($this->request);
        }
        return $this->applyActionResult($result);
    }
}