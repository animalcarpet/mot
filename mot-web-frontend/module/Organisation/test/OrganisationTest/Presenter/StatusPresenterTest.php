<?php

namespace OrganisationTest\Presenter;

use DvsaCommon\Configuration\MotConfig;
use Organisation\Presenter\StatusPresenter;
use Site\Service\RiskAssessmentScoreRagClassifier;

class StatusPresenterTest extends \PHPUnit_Framework_TestCase
{
    private $config;
    /** @var RiskAssessmentScoreRagClassifier */
    private $riskAssessmentScore;

    /**
     * @dataProvider dataProviderTestStatus
     *
     * @param $rag
     * @param $cssColor
     * @param $value
     */
    public function testStatusClassifier($rag, $cssColor, $value)
    {
        $this->mockConfig();
        $this->riskAssessmentScore = new RiskAssessmentScoreRagClassifier(0, $this->config);
        $this->riskAssessmentScore->setScore($rag);
        $statusPresenter = (new StatusPresenter())->getStatusFields($this->riskAssessmentScore->getRagScore());

        $this->assertEquals($statusPresenter->getSidebarBadgeCssClass(), $cssColor);
        $this->assertEquals($statusPresenter->getStatus(), $value);
    }

    public function dataProviderTestStatus()
    {
        return [
            [
                'rag' => 1,
                'cssColor' => 'badge--success',
                'value' => 'Green',
            ],
            [
                'rag' => 0.00,
                'cssColor' => 'badge',
                'value' => 'White',
            ],
            [
                'rag' => 0.01,
                'cssColor' => 'badge--success',
                'value' => 'Green',
            ],
            [
                'rag' => 0.11,
                'cssColor' => 'badge--success',
                'value' => 'Green',
            ],
            [
                'rag' => 324.1,
                'cssColor' => 'badge--success',
                'value' => 'Green',
            ],
            [
                'rag' => 324.11,
                'cssColor' => 'badge--warn',
                'value' => 'Amber',
            ],
            [
                'rag' => 459.20,
                'cssColor' => 'badge--warn',
                'value' => 'Amber',
            ],
            [
                'rag' => 459.21,
                'cssColor' => 'badge--alert',
                'value' => 'Red',
            ],
            [
                'rag' => 999.99,
                'cssColor' => 'badge--alert',
                'value' => 'Red',
            ],
        ];
    }

    protected function mockConfig()
    {
        $this->config = $this->getMockBuilder(MotConfig::class)->disableOriginalConstructor()->getMock();
        $returnMap = [
            ['site_assessment', 'green', 'start', 0.01],
            ['site_assessment', 'amber', 'start', 324.11],
            ['site_assessment', 'red', 'start', 459.21],
        ];

        $this->config->expects($this->any())->method('get')->will($this->returnValueMap($returnMap));
    }
}
