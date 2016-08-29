<?php

namespace TestSupport\Controller;

use TestSupport\Helper\TestDataResponseHelper;
use TestSupport\Service\GdsSurveyService;

class GdsSurveyController extends BaseTestSupportRestfulController
{
    public function create()
    {
        /** @var GdsSurveyService $surveyService */
        $surveyService = $this->getServiceLocator()->get(GdsSurveyService::class);
        return TestDataResponseHelper::jsonOk($surveyService->generateSurveyReports());
    }
}
