<?php

namespace Core\FormWizard;

use Core\TwoStepForm\TwoStepFormContainer;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use DvsaCommon\Guid\Guid;
use DvsaCommon\Utility\TypeCheck;
use Zend\Session\Container;

class FormContainer extends TwoStepFormContainer implements AutoWireableInterface
{
    public function store($sessionKey, array $formData, $formUuid = null)
    {
        TypeCheck::assertArray($formData);
        unset($formData['_csrf_token']);

        if ($formUuid === null) {
            $formUuid = Guid::newGuid();
        }

        $offset = $this->toOffset($sessionKey, $formUuid);
        $this->container->offsetSet($offset, $formData);

        return $formUuid;
    }

    public function dataExists($sessionKey, $formUuid)
    {
        if ($formUuid === null) {
            return false;
        }

        $offset = $this->toOffset($sessionKey, $formUuid);

        return $this->container->offsetExists($offset);
    }

    public function clear($sessionKey, $formUuid)
    {
        $offset = $this->toOffset($sessionKey, $formUuid);
        $this->container->offsetUnset($offset);

        return $this;
    }
}
