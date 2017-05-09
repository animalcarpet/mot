<?php

namespace DvsaMotApi\Service\Helper;

/**
 * Misc class for fiddling with strings. Probably could be done with 1-liners, but these add more validation.
 */
class StringHelper
{
    /**
     * For backward compatibility with APIs that provide inputs in old 'role' formats,
     * returns the appropriate vehicle class.
     *
     * @param $expectedPrefix             e.g. TESTER-CLASS-
     * @param $vehicleClassCodeWithPrefix e.g. TESTER-CLASS-1
     *
     * @return e.g. 1
     *
     * @throws \LogicException
     */
    public static function extractClassFromString($expectedPrefix, $vehicleClassCodeWithPrefix)
    {
        preg_match('/^'.$expectedPrefix.'(\d)$/', $vehicleClassCodeWithPrefix, $matches);

        if (!$matches) {
            throw new \InvalidArgumentException(
                'Expecting a string of the form '.$expectedPrefix."?, got [$vehicleClassCodeWithPrefix]"
            );
        }

        $vehicleClassCode = $matches[1];

        if (!$vehicleClassCode) {
            throw new \LogicException("Expecting vehicle class code, got [$vehicleClassCodeWithPrefix]");
        }

        return $vehicleClassCode;
    }
}
