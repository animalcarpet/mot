<?php

namespace SiteTest\Service;

use DvsaCommon\Configuration\MotConfig;
use Site\Service\RiskAssessmentScoreRagClassifier;

class RiskAssessmentScoreRagClassifierTest extends \PHPUnit_Framework_TestCase
{
    private $config;

    protected function setUp()
    {
        $this->mockConfig();
    }

    /**
     * @dataProvider testClassificationDataProvider
     */
    public function testScoreRagClassification($score, $expectedStatus)
    {
        $classifier = new RiskAssessmentScoreRagClassifier($score, $this->config);
        $result = $classifier->getRagScore();
        $this->assertSame($expectedStatus, $result);
    }

    public function testClassificationDataProvider()
    {
        return [
            [
                'score' => 0,
                'expectedStatus' => RiskAssessmentScoreRagClassifier::WHITE_STATUS,
            ],
            [
                'score' => '0',
                'expectedStatus' => RiskAssessmentScoreRagClassifier::WHITE_STATUS,
            ],
            [
                'score' => 0.1,
                'expectedStatus' => RiskAssessmentScoreRagClassifier::GREEN_STATUS,
            ],
            [
                'score' => '0.1',
                'expectedStatus' => RiskAssessmentScoreRagClassifier::GREEN_STATUS,
            ],
            [
                'score' => 0.11,
                'expectedStatus' => RiskAssessmentScoreRagClassifier::GREEN_STATUS,
            ],
            [
                'score' => 0.99,
                'expectedStatus' => RiskAssessmentScoreRagClassifier::GREEN_STATUS,
            ],
            [
                'score' => 199.32,
                'expectedStatus' => RiskAssessmentScoreRagClassifier::GREEN_STATUS,
            ],
            [
                'score' => 324.10,
                'expectedStatus' => RiskAssessmentScoreRagClassifier::GREEN_STATUS,
            ],
            [
                'score' => 424.11,
                'expectedStatus' => RiskAssessmentScoreRagClassifier::AMBER_STATUS,
            ],
            [
                'score' => '424.11',
                'expectedStatus' => RiskAssessmentScoreRagClassifier::AMBER_STATUS,
            ],
            [
                'score' => 459.20,
                'expectedStatus' => RiskAssessmentScoreRagClassifier::AMBER_STATUS,
            ],
            [
                'score' => '459.20',
                'expectedStatus' => RiskAssessmentScoreRagClassifier::AMBER_STATUS,
            ],
            [
                'score' => 459.21,
                'expectedStatus' => RiskAssessmentScoreRagClassifier::RED_STATUS,
            ],
            [
                'score' => 459.22,
                'expectedStatus' => RiskAssessmentScoreRagClassifier::RED_STATUS,
            ],
            [
                'score' => 899.99,
                'expectedStatus' => RiskAssessmentScoreRagClassifier::RED_STATUS,
            ],
            [
                'score' => 999.99,
                'expectedStatus' => RiskAssessmentScoreRagClassifier::RED_STATUS,
            ],
        ];
    }

    /**
     * @dataProvider invalidInputsDataProvider
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Score should be an numeric value
     */
    public function testItShouldThrowExceptionOnNotANumberProvided($value)
    {
        $classifier = new RiskAssessmentScoreRagClassifier($value, $this->config);
    }

    public function invalidInputsDataProvider()
    {
        return [
            ['value' => 'test'],
            ['value' => '234sdfa234'],
            ['value' => '32.32daa'],
            ['value' => null],
            ['value' => true],
            ['value' => false],
            ['value' => new \StdClass()],
            ['value' => []],
        ];
    }

    protected function mockConfig()
    {
        $this->config = $this->getMockBuilder(MotConfig::class)->disableOriginalConstructor()->getMock();
        $returnMap = [
            ['site_assessment', 'green', 'start', 0],
            ['site_assessment', 'amber', 'start', 324.11],
            ['site_assessment', 'red', 'start', 459.21],
        ];

        $this->config->expects($this->any())->method('get')->will($this->returnValueMap($returnMap));
    }
}
