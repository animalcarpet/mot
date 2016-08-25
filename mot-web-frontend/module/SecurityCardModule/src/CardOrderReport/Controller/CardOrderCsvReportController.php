<?php

namespace Dvsa\Mot\Frontend\SecurityCardModule\CardOrderReport\Controller;

use Core\Controller\AbstractDvsaActionController;
use Dvsa\Mot\Frontend\SecurityCardModule\CardOrderReport\Action\CardOrderCsvReportAction;
use Zend\Http\Headers;
use Zend\Http\Response\Stream;
use Zend\View\Model\ViewModel;

class CardOrderCsvReportController extends AbstractDvsaActionController
{
    /** @var CardOrderCsvReportAction  */
    private $csvReportAction;

    public function __construct(
        CardOrderCsvReportAction $csvReportAction
    ) {
        $this->csvReportAction = $csvReportAction;
    }

    public function downloadCsvAction()
    {
        $actionResult = $this->csvReportAction->execute($this->params('date'));
        return $this->applyActionResult($actionResult);
    }
}