<?php

namespace DvsaMotApiTest\Factory\Service\Validator;

use CensorApi\Service\CensorService;
use DvsaCommonTest\TestUtils\TestCaseTrait;
use DvsaCommonTest\TestUtils\XMock;
use DvsaMotApi\Factory\Service\Validator\ReplacementCertificateDraftChangeValidatorFactory;
use DvsaMotApi\Service\Validator\ReplacementCertificateDraftChangeValidator;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class ReplacementCertificateDraftChangeValidatorFactoryTest.
 */
class ReplacementCertificateDraftChangeValidatorFactoryTest extends \PHPUnit_Framework_TestCase
{
    use TestCaseTrait;

    public function testFactoryServiceGetList()
    {
        $mockServiceLocator = XMock::of(ServiceLocatorInterface::class, ['get']);
        $this->mockMethod($mockServiceLocator, 'get', $this->at(0), XMock::of(CensorService::class));

        $this->assertInstanceOf(
            ReplacementCertificateDraftChangeValidator::class,
            (new ReplacementCertificateDraftChangeValidatorFactory())->createService($mockServiceLocator)
        );
    }
}
