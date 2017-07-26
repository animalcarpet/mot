<?php


namespace SiteApi\Factory\Service;


use Doctrine\ORM\EntityManager;
use DvsaEntities\Entity\SiteBusinessRoleMap;
use SiteApi\Mapper\TestersAnnualAssessmentMapper;
use SiteApi\Service\TestersAnnualAssessmentService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class TestersAnnualAssessmentServiceFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $serviceLocator
     * @return TestersAnnualAssessmentService
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /** @var EntityManager $em */
        $em = $serviceLocator->get(EntityManager::class);

        return new TestersAnnualAssessmentService(
            $em->getRepository(SiteBusinessRoleMap::class),
            $serviceLocator->get(TestersAnnualAssessmentMapper::class),
            $serviceLocator->get('DvsaAuthorisationService')
        );
    }
}