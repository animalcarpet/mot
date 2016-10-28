<?php
namespace Dvsa\Mot\Behat\Support\Data\Transformer;

use Dvsa\Mot\Behat\Support\Scope\BeforeBehatScenarioScope;

trait TypeConversion
{
    /**
     * @Transform /^[-]?[0-9]+$/
     */
    public function castToInteger($string)
    {
        if (BeforeBehatScenarioScope::isTransformerDisabled()) {
            return $string;
        }

        return intval($string);
    }

    /**
     * @Transform /^[-]?[0-9]*\.[0-9]+$/
     */
    public function castToFloat($string)
    {
        if (BeforeBehatScenarioScope::isTransformerDisabled()) {
            return $string;
        }

        return floatval($string);
    }

    /**
     * @Transform /^true|TRUE|false|FALSE+$/
     */
    public function castToBool($string)
    {
        if (BeforeBehatScenarioScope::isTransformerDisabled()) {
            return $string;
        }

        if (strtolower($string) === "true") {
            return true;
        }

        return false;
    }

    /**
     * @Transform :startDate
     * @Transform :endDate
     * @Transform :date
     */
    public function castToDateTime($dateTime)
    {
        if (BeforeBehatScenarioScope::isTransformerDisabled()) {
            return $dateTime;
        }

        return new \DateTime($dateTime);
    }
}
