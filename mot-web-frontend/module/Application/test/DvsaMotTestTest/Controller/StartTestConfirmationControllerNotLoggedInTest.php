<?php

namespace DvsaMotTestTest\Controller;

use DvsaCommon\Obfuscate\EncryptionKey;
use DvsaCommon\Obfuscate\ParamEncoder;
use DvsaCommon\Obfuscate\ParamEncrypter;
use DvsaCommon\Obfuscate\ParamObfuscator;
use DvsaCommonTest\Bootstrap;
use Dvsa\Mot\Frontend\Test\StubIdentityAdapter;
use DvsaMotTest\Controller\StartTestConfirmationController;

/**
 * Class StartTestConfirmationControllerTest.
 */
class StartTestConfirmationControllerNotLoggedInTest extends AbstractDvsaMotTestTestCase
{
    protected function setUp()
    {
        $this->setServiceManager(Bootstrap::getServiceManager());
        $this->setController(new StartTestConfirmationController($this->createParamObfuscator()));
        parent::setUp();
    }

    /**
     * @dataProvider provideDataForTestDivision
     *
     * @param string $actionName
     *
     * @expectedException \DvsaCommon\Exception\UnauthorisedException
     */
    public function testIndexActionUnauthenticated($actionName)
    {
        $this->setupAuthenticationServiceForIdentity(StubIdentityAdapter::asAnonymous());

        $this->getResponseForAction($actionName, ['id' => '1']);
    }

    public function provideDataForTestDivision()
    {
        return [
            ['actionName' => 'index'],
            ['actionName' => 'retest'],
        ];
    }

    /**
     * @return ParamObfuscator
     */
    protected function createParamObfuscator()
    {
        $config = ['security' => ['obfuscate' => ['key' => 'ggg', 'entries' => ['vehicleId' => true]]]];
        $paramEncrypter = new ParamEncrypter(new EncryptionKey($config['security']['obfuscate']['key']));
        $paramEncoder   = new ParamEncoder();

        return new ParamObfuscator($paramEncrypter, $paramEncoder, $config);
    }
}
