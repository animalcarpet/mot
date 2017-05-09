<?php

namespace DvsaMotTestTest\Controller;

use Application\Service\CatalogService;
use Core\Catalog\CountryOfRegistration\CountryOfRegistrationCatalog;
use Dvsa\Mot\ApiClient\Service\VehicleService;
use DvsaCommon\Obfuscate\EncryptionKey;
use DvsaCommon\Obfuscate\ParamEncoder;
use DvsaCommon\Obfuscate\ParamEncrypter;
use DvsaCommon\Obfuscate\ParamObfuscator;
use DvsaCommonTest\Bootstrap;
use Dvsa\Mot\Frontend\Test\StubIdentityAdapter;
use DvsaCommonTest\TestUtils\XMock;
use DvsaMotTest\Controller\StartTestConfirmationController;
use DvsaCommon\HttpRestJson\Client;
use Application\Factory\ApplicationWideCacheFactory;
use DvsaMotTest\Service\StartTestChangeService;

/**
 * Class StartTestConfirmationControllerTest.
 */
class StartTestConfirmationControllerNotLoggedInTest extends AbstractDvsaMotTestTestCase
{
    protected function setUp()
    {
        $this->setServiceManager(Bootstrap::getServiceManager());
        $vehicleService = XMock::of(VehicleService::class);
        $this->setController(new StartTestConfirmationController(
            $this->createParamObfuscator(),
            $this->createCountryOfRegistrationCatalog(),
            $vehicleService,
            XMock::of(StartTestChangeService::class)
        ));
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
        $paramEncoder = new ParamEncoder();

        return new ParamObfuscator($paramEncrypter, $paramEncoder, $config);
    }

    /**
     * @return CountryOfRegistrationCatalog
     */
    protected function createCountryOfRegistrationCatalog()
    {
        $fixture = json_decode(
            file_get_contents(
                __DIR__.'/../../DvsaMotEnforcementTest/Controller/fixtures/catalog.json'
            ),
            true
        );

        $client = \DvsaCommonTest\TestUtils\XMock::of(Client::class);
        $client
            ->expects($this->any())
            ->method('get')
            ->willReturn($fixture);

        $appCacheFactory = new ApplicationWideCacheFactory();
        $appCache = $appCacheFactory->createService(Bootstrap::getServiceManager());
        $this->service = new CatalogService($appCache, $client);

        $catalogService = new CatalogService($appCache, $client);

        return new CountryOfRegistrationCatalog($catalogService);
    }
}
