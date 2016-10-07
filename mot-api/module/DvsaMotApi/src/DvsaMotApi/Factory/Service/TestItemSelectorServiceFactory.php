<?php

namespace DvsaMotApi\Factory\Service;

use Doctrine\ORM\EntityManager;
use DvsaCommon\Configuration\MotConfig;
use DvsaEntities\Repository\TestItemCategoryRepository;
use DvsaMotApi\Formatting\DefectSentenceCaseConverter;
use DvsaMotApi\Service\TestItemSelectorService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class TestItemSelectorServiceFactory.
 */
class TestItemSelectorServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new TestItemSelectorService(
            $serviceLocator->get(EntityManager::class),
            $serviceLocator->get('Hydrator'),
            $serviceLocator->get('RfrRepository'),
            $serviceLocator->get('DvsaAuthorisationService'),
            $serviceLocator->get(TestItemCategoryRepository::class),
            $serviceLocator->get(MotConfig::class)->withDefault([])->get('disabled_rfrs'),
            $serviceLocator->get('Feature\FeatureToggles'),
            $serviceLocator->get(DefectSentenceCaseConverter::class)
        );
    }
}
