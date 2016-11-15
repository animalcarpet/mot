<?php
namespace Dvsa\Mot\Behat\Support\Data\Transformer;

trait TypeConversion
{
    /**
     * @Transform /^[-]?[0-9]+$/
     */
    public function castToInteger($string)
    {
        if ($string != intval($string)) {
            return $string;
        }

        return intval($string);
    }

    /**
     * @Transform /^[-]?[0-9]*\.[0-9]+$/
     */
    public function castToFloat($string)
    {
        if ($string != floatval($string) ) {
            return $string;
        }

        return floatval($string);
    }

    /**
     * @Transform /^true|TRUE|false|FALSE+$/
     */
    public function castToBool($string)
    {
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
        return new \DateTime($dateTime);
    }
}
