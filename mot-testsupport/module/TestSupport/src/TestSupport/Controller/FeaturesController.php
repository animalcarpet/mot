<?php

namespace TestSupport\Controller;

use TestSupport\Service\FeaturesService;

class FeaturesController extends BaseTestSupportRestfulController
{
    protected $identifierName = 'featureName';

    public function get($featureName)
    {
        $featuresService = $this->getServiceLocator()->get(FeaturesService::class);

        return $featuresService->get($featureName);
    }
}
