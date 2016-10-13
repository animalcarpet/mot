<?php

namespace Core\TwoStepForm;

use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use DvsaCommon\Guid\Guid;
use DvsaCommon\Utility\TypeCheck;
use Zend\Session\Container;

/**
 * Will store form data for forms that span across two screens
 *
 * Class TwoStepFormContainer
 * @package Core\TwoStepForm
 */
class TwoStepFormContainer implements AutoWireableInterface
{
    const SESSION_CONTAINER_KEY = "FORM_SESSION_CONTAINER";

    protected $container;

    public function __construct()
    {
        $this->container = new Container(self::SESSION_CONTAINER_KEY);
    }

    /**
     * @param $sessionKey
     * @param array $formData
     * @return string uuid of the stored form
     */
    public function store($sessionKey, array $formData)
    {
        TypeCheck::assertArray($formData);
        unset($formData['_csrf_token']);

        $formUuid = Guid::newGuid();

        $offset = $this->toOffset($sessionKey, $formUuid);
        $this->container->offsetSet($offset, $formData);

        return $formUuid;
    }

    public function get($formUuid, $sessionKey)
    {
        $offset = $this->toOffset($sessionKey, $formUuid);

        return $this->container->offsetGet($offset);
    }

    protected function toOffset($sessionKey, $formUuid)
    {
        return "$sessionKey/$formUuid";
    }
}
