<?php

namespace Dvsa\Mot\Frontend\SecurityCardModule\CardActivation\Action;

use Core\Action\ViewActionResult;
use Core\Action\NotFoundActionResult;
use Dvsa\Mot\Frontend\SecurityCardModule\CardActivation\Service\RegisterCardHardStopCondition;
use Dvsa\Mot\Frontend\SecurityCardModule\CardActivation\ViewModel\RegisterCardHardStopViewModel;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use DvsaFeature\FeatureToggles;
use Zend\Http\Request;
use Zend\View\Model\ViewModel;

class RegisterCardHardStopAction implements AutoWireableInterface
{
    /** @var RegisterCardHardStopCondition */
    private $condition;

    private $helpdeskConfig;

    public function __construct(RegisterCardHardStopCondition $condition, $helpdeskConfig)
    {
        $this->condition = $condition;
        $this->helpdeskConfig = $helpdeskConfig;
    }

    public function execute()
    {
        if (!$this->condition->isTrue()) {
            return new NotFoundActionResult();
        }

        $result = new ViewActionResult();
        $result->setViewModel((new RegisterCardHardStopViewModel())
            ->setHelpdeskConfig($this->helpdeskConfig));
        $result->setTemplate('2fa/register-card/hard-stop');

        return $result;
    }
}