<?php
/**
 * This file is part of the DVSA MOT API project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */

namespace Dvsa\Mot\Api\RegistrationModule\Service;

/**
 * Utility to convert provided step names by the WEB tier to respective inputFilter name and vice versa.
 *
 * Class ValidatorKeyConverter
 */
class ValidatorKeyConverter
{
    const STEP_NAME_PREFIX = 'step';
    const INPUT_FILTER_SUFFIX = 'InputFilter';
    const INPUT_FILTER_PREFIX = 'DvsaCommon\InputFilter\Registration\\';

    /**
     * Convert main keys on the given array from step names to inputFilter names
     * e.g. ["stepDetails" => [...]] to ["DvsaCommon\InputFilter\Registration\DetailsInputFilter" =>[...]].
     *
     * @param array $data
     *
     * @return array
     */
    public static function stepsToInputFilters($data)
    {
        $withConvertedKeysToInputFilter = [];

        foreach ($data as $stepName => $items) {
            $withConvertedKeysToInputFilter[self::stepToInputFilter($stepName)] = $items;
        }

        return $withConvertedKeysToInputFilter;
    }

    /**
     * Convert main keys on the given array from inputFilter names to step names
     * e.g. ["DvsaCommon\InputFilter\Registration\DetailsInputFilter"=>[...]] to ["stepDetails"=>[...]].
     *
     * @param array $data
     *
     * @return array
     */
    public static function inputFiltersToSteps($data)
    {
        $withConvertedKeysToStep = [];

        foreach ($data as $stepName => $items) {
            $withConvertedKeysToStep[self::inputFilterToStep($stepName)] = $items;
        }

        return $withConvertedKeysToStep;
    }

    /**
     * Convert string from step name to inputFilter name
     * e.g "stepDetails" to "DvsaCommon\InputFilter\Registration\DetailsInputFilter".
     *
     * @param string $stepName
     *
     * @return string
     */
    public static function stepToInputFilter($stepName)
    {
        $inputFilterName = self:: INPUT_FILTER_PREFIX . self::stripName($stepName) . self::INPUT_FILTER_SUFFIX;

        return $inputFilterName;
    }

    /**
     * Convert string from inputFilter name to step name
     * e.g. "stepDetails" to "DvsaCommon\InputFilter\Registration\DetailsInputFilter".
     *
     * @param string $inputFilterName
     *
     * @return string
     */
    public static function inputFilterToStep($inputFilterName)
    {
        $stepName = self::STEP_NAME_PREFIX . self::stripName($inputFilterName);

        return $stepName;
    }

    /**
     * Remove all the known prefixes and suffixes from the given string.
     *
     * @param string $name
     *
     * @return string
     */
    private static function stripName($name)
    {
        $name = str_replace(
            [
                self::STEP_NAME_PREFIX,
                self::INPUT_FILTER_PREFIX,
                self::INPUT_FILTER_SUFFIX,
            ],
            ['', '', ''],
            $name);

        return $name;
    }
}
