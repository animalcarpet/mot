<?php

namespace DvsaCommonTest\Factory\Formatting;

use DvsaMotApi\Factory\Formatting\DefectSentenceCaseConverterFactory;
use DvsaMotApi\Formatting\DefectSentenceCaseConverter;
use Zend\ServiceManager\ServiceManager;

class DefectSentenceCaseConverterFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testServiceIsCreated()
    {
        $factory = new DefectSentenceCaseConverterFactory();
        $defectSentenceCaseConverter = $factory->createService(new ServiceManager());

        $this->assertInstanceOf(DefectSentenceCaseConverter::class, $defectSentenceCaseConverter);
    }
}
