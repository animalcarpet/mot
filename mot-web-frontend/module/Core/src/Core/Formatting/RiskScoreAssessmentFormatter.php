<?php


namespace Core\Formatting;


class RiskScoreAssessmentFormatter
{

    /*
     * Formats risk score - drops everything that's after 1dp, without rounding up
     */
    public static function formatRiskScore($score)
    {
        return ((int)($score * 10)) / 10;
    }
}