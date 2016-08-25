<?php

namespace Dvsa\Mot\Frontend\SecurityCardModule\CardActivation\Action;

use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use DvsaFeature\FeatureToggles;
use Zend\Http\Request;
use Zend\View\Model\ViewModel;

class RegisterCardGetAction extends RegisterCardAction implements AutoWireableInterface
{

    public function doExecute(Request $request)
    {
        return $this->defaultActionResult();
    }
}