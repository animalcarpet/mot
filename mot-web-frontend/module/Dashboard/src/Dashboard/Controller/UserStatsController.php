<?php

namespace Dashboard\Controller;

use Core\Controller\AbstractAuthActionController;
use Dashboard\Action\UserStatsAction;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;

/**
 * Class UserStatsController.
 */
class UserStatsController extends AbstractAuthActionController implements AutoWireableInterface
{
    const ROUTE_USER_STATS = 'user-home/stats';

    private $action;

    public function __construct(
        UserStatsAction $action
    ) {
        $this->action = $action;
    }

    public function showAction()
    {
        $userId = $this->getIdentity()->getUserId();

        return $this->applyActionResult(
            $this->action->execute($userId)
        );
    }
}
