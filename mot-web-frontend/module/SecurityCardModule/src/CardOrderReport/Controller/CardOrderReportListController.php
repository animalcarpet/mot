<?php

namespace Dvsa\Mot\Frontend\SecurityCardModule\CardOrderReport\Controller;

use Core\Controller\AbstractDvsaActionController;
use Dvsa\Mot\Frontend\SecurityCardModule\CardOrderReport\Action\CardOrderReportListAction;

class CardOrderReportListController extends AbstractDvsaActionController
{
    private $listAction;

    public function __construct(
        CardOrderReportListAction $listAction
    ) {
        $this->listAction = $listAction;
    }

    public function listAction()
    {
        $result = $this->listAction->execute();

        return $this->applyActionResult($result);
    }
}
