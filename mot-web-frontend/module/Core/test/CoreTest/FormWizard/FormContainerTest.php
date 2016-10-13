<?php
namespace CoreTest\FormWizard;

use Core\FormWizard\FormContainer;

class FormContainerTest extends  \PHPUnit_Framework_TestCase
{
    public function testStoreReturnsNewFormUuid()
    {
        $formUuid = "adasdas-asdasd-dsdasd";
        $sessionKey = uniqid();

        $container = new FormContainer();
        $newFormUuid = $container->store($sessionKey, ["somedata"], null);

        $this->assertNotEquals($newFormUuid, $formUuid);
    }

    public function testStoreReturnsTheSameFormUuid()
    {
        $formUuid = uniqid();
        $sessionKey = uniqid();

        $container = new FormContainer();
        $newFormUuid = $container->store($sessionKey, ["somedata"], $formUuid);

        $this->assertEquals($newFormUuid, $formUuid);
    }

    public function testDataExistsReturnsFalseWhenDataDoesNotExist()
    {
        $formUuid = uniqid();
        $sessionKey = uniqid();

        $container = new FormContainer();
        $this->assertFalse($container->dataExists($sessionKey, $formUuid));
    }

    public function testDataExistsReturnsTrueWhenDataExists()
    {
        $formUuid = uniqid();
        $sessionKey = uniqid();
        $data = ["somedata"];

        $container = new FormContainer();
        $container->store($sessionKey, $data, $formUuid);

        $this->assertTrue($container->dataExists($sessionKey, $formUuid));
    }

    public function testClearRemovesDataFromSession()
    {
        $formUuid = uniqid();
        $sessionKey = uniqid();

        $container = new FormContainer();
        $container->store($sessionKey, ["somedata"], $formUuid);
        $container->clear($sessionKey, $formUuid);

        $this->assertFalse($container->dataExists($sessionKey, $formUuid));
    }
}