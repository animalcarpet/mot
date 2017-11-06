<?php

namespace Dvsa\Mot\Frontend\MotTestModule\Factory\Service;

use Dvsa\Mot\Frontend\MotTestModule\Service\SearchReasonForRejectionService;
use DvsaCommon\ApiClient\ReasonForRejection\ReasonForRejectionApiResource;
use DvsaCommon\Auth\MotAuthorisationServiceInterface;
use Dvsa\Mot\ApiClient\Service\MotTestService;
use DvsaCommon\Constants\FeatureToggle;
use DvsaCommon\ReasonForRejection\ElasticSearch\ReasonForRejectionElasticSearchClient;
use DvsaFeature\FeatureToggles;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class SearchReasonForRejectionServiceFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return SearchReasonForRejectionService
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /** @var MotTestService $motTestService */
        $motTestService = $serviceLocator->get(MotTestService::class);

        /** @var MotAuthorisationServiceInterface $motAuthorisationService */
        $motAuthorisationService = $serviceLocator->get(MotAuthorisationServiceInterface::class);

        /** @var FeatureToggles $featureToggles */
        $featureToggles = $serviceLocator->get('Feature\FeatureToggles');

        if ($featureToggles->isEnabled(FeatureToggle::RFR_ELASTICSEARCH)) {
            /** @var ReasonForRejectionElasticSearchClient $client */
            $client = $serviceLocator->get(ReasonForRejectionElasticSearchClient::class);
        } else {
            /** @var ReasonForRejectionApiResource$client */
            $client = $serviceLocator->get(ReasonForRejectionApiResource::class);
        }

        return new SearchReasonForRejectionService($motTestService, $motAuthorisationService, $client);
    }
}