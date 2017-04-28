<?php

namespace Dvsa\Mot\Frontend\SecurityCardModule\CardActivation\Action;

use Core\Action\ViewActionResult;
use Core\Action\NotFoundActionResult;
use Dvsa\Mot\Frontend\AuthenticationModule\Controller\LogoutController;
use Dvsa\Mot\Frontend\SecurityCardModule\CardActivation\Controller\RegisterCardController;
use Dvsa\Mot\Frontend\SecurityCardModule\CardActivation\Service\RegisterCardHardStopCondition;
use Dvsa\Mot\Frontend\SecurityCardModule\CardActivation\ViewModel\RegisterCardHardStopViewModel;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;

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

        $viewModel = new RegisterCardHardStopViewModel();
        $viewModel->setRegisterRoute(RegisterCardController::ROUTE_REGISTER_CARD);
        $viewModel->setHelpdeskConfig($this->helpdeskConfig);
        $viewModel->setLogoutRoute(LogoutController::ROUTE_LOGOUT);

        $result = new ViewActionResult();
        $result->setViewModel($viewModel);
        $result->setTemplate('2fa/register-card/activate-card-hard-stop.twig');

        return $result;
    }
}
