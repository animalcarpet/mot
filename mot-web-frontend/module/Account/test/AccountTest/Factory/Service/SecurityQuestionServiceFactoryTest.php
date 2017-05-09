<?php

namespace AccountTest\Factory\Service;

use Account\Factory\Service\SecurityQuestionServiceFactory;
use Account\Service\SecurityQuestionService;
use CoreTest\Service\StubMapperFactory;
use DvsaClient\Mapper\AccountMapper;
use DvsaClient\Mapper\PersonMapper;
use DvsaClient\Mapper\UserAdminMapper;
use DvsaClient\MapperFactory;
use DvsaCommon\Obfuscate\ParamObfuscator;
use DvsaCommonTest\TestUtils\TestCaseTrait;
use DvsaCommonTest\TestUtils\XMock;
use UserAdmin\Service\UserAdminSessionManager;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class SecurityQuestionServiceFactoryTest.
 */
class SecurityQuestionServiceFactoryTest extends \PHPUnit_Framework_TestCase
{
    use TestCaseTrait;

    public function testEventServiceGetList()
    {
        $mockServiceLocator = XMock::of(ServiceLocatorInterface::class, ['get']);

        $mapperFactory = new StubMapperFactory([
            MapperFactory::PERSON => XMock::of(PersonMapper::class),
            MapperFactory::USER_ADMIN => XMock::of(UserAdminMapper::class),
            MapperFactory::ACCOUNT => XMock::of(AccountMapper::class),
        ]);

        $this->mockMethod($mockServiceLocator, 'get', $this->at(0), $mapperFactory);
        $this->mockMethod($mockServiceLocator, 'get', $this->at(1), XMock::of(UserAdminSessionManager::class));
        $this->mockMethod($mockServiceLocator, 'get', $this->at(2), XMock::of(ParamObfuscator::class));

        $factory = new SecurityQuestionServiceFactory();

        $this->assertInstanceOf(
            SecurityQuestionService::class,
            $factory->createService($mockServiceLocator)
        );
    }
}
