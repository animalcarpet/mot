<?php

namespace DvsaCommonTest\TestUtils;

/**
 * Class MethodSpy
 *
 * Uses PhpUnit to create a spy for the given method of the given mock.
 * Allows to check parameters passed to mocks AFTER the mocked method is called.
 * (In contradiction to classic mocking, where the checks are defined before the call)
 *
 * @package DvsaCommonTest\TestUtils
 */
class MethodSpy
{
    private $invoker;

    private $invocationMocker;

    /**
     * @param \PHPUnit_Framework_MockObject_MockObject $mock
     * @param string $method
     */
    public function  __construct($mock, $method)
    {
        $this->invoker = new \PHPUnit_Framework_MockObject_Matcher_AnyInvokedCount();

        $this->invocationMocker = $mock->expects($this->invoker)->method($method);
    }

    public function mock()
    {
        return $this->invocationMocker;
    }

    public function invocationCount()
    {
        $invocations = $this->getInvocations();

        return count ($invocations);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_Invocation_Object[]
     */
    private function getInvocations()
    {
        return $this->invoker->getInvocations();
    }

    public function paramsForInvocation($index)
    {
        return $this->getInvocations()[$index]->parameters;
    }

    public function paramsForLastInvocation()
    {
        return $this->paramsForInvocation($this->invocationCount()-1);
    }
}
