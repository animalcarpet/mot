<?php

namespace CoreTest\FormWizard\Fake;

use Core\Action\RedirectToRoute;
use Core\FormWizard\AbstractStep;

class FakeStep extends AbstractStep
{
    const RESPONSE_GET = 'get';
    const RESPONSE_POST = 'post';

    private $name;
    private $storedData;
    private $isValid;

    public function __construct($name, $isValid, array $storedData = [])
    {
        parent::__construct();
        $this->name = $name;
        $this->storedData = $storedData;
        $this->isValid = $isValid;
    }

    public function getName()
    {
        return $this->name;
    }

    public function executeGet($formUuid = null)
    {
        return self::RESPONSE_GET.' - '.$this->name;
    }

    public function executePost(array $formData, $formUuid = null)
    {
        return self::RESPONSE_POST.' - '.$this->name;
    }

    public function isValid($formUuid)
    {
        return $this->isValid;
    }

    public function getStoredData($formUuid)
    {
        return $this->storedData;
    }

    public function getRoute(array $queryParams = [])
    {
        return new RedirectToRoute($this->name, [], $queryParams);
    }
}
