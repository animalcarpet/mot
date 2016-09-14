<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */

namespace DvsaMotApiTest\Controller;

use DvsaMotApi\Controller\SurveyController;
use DvsaMotApi\Service\SurveyService;

class SurveyControllerTest extends AbstractMotApiControllerTestCase
{
    /**
     * @var SurveyService
     */
    private $surveyService;

    protected function setUp()
    {
        $this->surveyService = $this
            ->getMockBuilder(SurveyService::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->controller = new SurveyController($this->surveyService);
        parent::setUp();
    }

    public function testMarkSurveyAsPresentedAction()
    {
    }
}
