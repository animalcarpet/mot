<?php
namespace Dvsa\Mot\Frontend\PersonModule\Breadcrumbs;

use Dvsa\Mot\Frontend\PersonModule\Routes\PersonProfileRoutes;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use DvsaCommon\Utility\TypeCheck;
use Zend\Mvc\Controller\AbstractActionController;

class TestQualityBreadcrumbs implements AutoWireableInterface
{
    private $personProfileBreadcrumbs;
    private $personProfileRoutes;

    public function __construct(
        PersonProfileBreadcrumbs $personProfileBreadcrumbs,
        PersonProfileRoutes $personProfileRoutes
    ) {
        $this->personProfileBreadcrumbs = $personProfileBreadcrumbs;
        $this->personProfileRoutes = $personProfileRoutes;
    }

    public function getBreadcrumbs($personId, AbstractActionController $controller, $currentStep)
    {
        $breadcrumbs = $this->personProfileBreadcrumbs->getBreadcrumbs($personId, $controller);
        $breadcrumbs += [
            "Test quality information" => $controller->url()->fromRoute(
                $this->personProfileRoutes->getTestQualityRoute(),
                $controller->params()->fromRoute()
            ),
        ];

        $breadcrumbs += [$currentStep => ''];

        return $breadcrumbs;
    }
}