<?php

namespace Dvsa\Mot\Frontend\SecurityCardModule\CardActivation\Action;

use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use Zend\Http\Request;

class RegisterCardGetAction extends RegisterCardAction implements AutoWireableInterface
{
    public function doExecute(Request $request)
    {
        return $this->defaultActionResult();
    }
}
