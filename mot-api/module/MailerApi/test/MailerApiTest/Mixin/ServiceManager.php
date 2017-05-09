<?php

namespace MailerApiTest\Mixin;

use DvsaCommonTest\Bootstrap;
use DvsaCommonTest\TestUtils\XMock;

trait ServiceManager
{
    private $serviceManager;

    protected function prepServiceManager()
    {
        $this->serviceManager = Bootstrap::getServiceManager();
        $this->serviceManager->setAllowOverride(true);

        return $this;
    }

    protected function sm()
    {
        return $this->serviceManager;
    }

    protected function setService($name, $handler)
    {
        $this->serviceManager->setService($name, $handler);
    }

    protected function setConfig($config)
    {
        $this->setService('Config', ['mailer' => $config]);
    }

    /**
     * Create a mocked class and attach it as a named service.
     *
     * @param string $classname    contains the class to be mocked
     * @param array  $expectedFuns functions you want to set expectations on later
     *
     * @return mixed
     */
    protected function setMockServiceClass($classname, $expectedFuns)
    {
        $mock = XMock::of($classname, $expectedFuns);
        $this->setService($classname, $mock);

        return $mock;
    }
}
