<?php

namespace DvsaCommonTest\Factory\Formatting;

use DvsaCommon\Factory\Formatting\DefectSentenceCaseConverterFactory;
use DvsaCommon\Formatting\DefectSentenceCaseConverter;
use DvsaFeature\FeatureToggles;
use Zend\ServiceManager\ServiceManager;

class DefectSentenceCaseConverterFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testServiceIsCreated()
    {
        $serviceManager = new ServiceManager();

        $featureToggleMock = $this
            ->getMockBuilder(FeatureToggles::class)
            ->disableOriginalConstructor()
            ->getMock();

        $serviceManager->setService('Feature\FeatureToggles', $featureToggleMock);

        $factory = new DefectSentenceCaseConverterFactory();
        $defectSentenceCaseConverter = $factory->createService($serviceManager);

        $this->assertInstanceOf(DefectSentenceCaseConverter::class, $defectSentenceCaseConverter);
    }
}
