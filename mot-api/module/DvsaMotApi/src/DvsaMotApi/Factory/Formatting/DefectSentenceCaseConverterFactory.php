<?php

namespace DvsaMotApi\Factory\Formatting;

use DvsaMotApi\Formatting\DefectSentenceCaseConverter;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Factory for DefectSentenceCaseConverterFactory
 */
class DefectSentenceCaseConverterFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new DefectSentenceCaseConverter();
    }
}
