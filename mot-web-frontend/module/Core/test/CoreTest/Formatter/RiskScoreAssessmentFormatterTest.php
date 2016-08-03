<?php


namespace CoreTest\Formatter;


use Core\Formatting\RiskScoreAssessmentFormatter;

class RiskScoreAssessmentFormatterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider dataProviderFormatRiskScore
     */
    public function testFormatRiskScore($riskScore, $expectedScore)
    {
        $this->assertEquals($expectedScore, RiskScoreAssessmentFormatter::formatRiskScore($riskScore));
    }

    public function dataProviderFormatRiskScore()
    {
        return [
            [1, 1],
            [0.00, 0.0],
            [-1.47, -1.4],
            [-1.11, -1.1],
            [0.11, 0.1],
            [0.57, 0.5],
            [0.99, 0.9],
            [0.9999999, 0.9],
            [100.17, 100.1],
            [100.87, 100.8],
        ];
    }
}