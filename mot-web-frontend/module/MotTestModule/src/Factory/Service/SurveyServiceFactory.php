<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */

namespace Dvsa\Mot\Frontend\MotTestModule\Factory\Service;

use Core\Service\SessionService;
use Dvsa\Mot\Frontend\MotTestModule\Service\SurveyService;
use DvsaClient\MapperFactory;
use DvsaCommon\HttpRestJson\Client as HttpClient;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Session\Container;

class SurveyServiceFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return SurveyService
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /** @var HttpClient $httpClient */
        $httpClient = $serviceLocator->get(HttpClient::class);

        /** @var MapperFactory $mapper */
        $mapper = $serviceLocator->get(MapperFactory::class);

        /** @var SessionService $sessionService */
        $sessionService = new SessionService((new Container(SessionService::UNIQUE_KEY)), $mapper);

        return new SurveyService($httpClient, $sessionService);
    }
}
