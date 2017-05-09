<?php

namespace DvsaMotTestTest\TestHelper;

/**
 * Class Fixture.
 */
class Fixture
{
    /**
     * Returns the content of a fixture file.
     *
     * @param string $fixtureFileName
     *
     * @throws \BadMethodCallException
     *
     * @return string
     */
    public static function getJsonFromFile($fixtureFileName)
    {
        $fixture = __DIR__.'/../Controller/fixtures/'.$fixtureFileName;

        if (!file_exists($fixture) || !is_readable($fixture)) {
            throw new \BadMethodCallException('Fixture file '.$fixture.' not found');
        }

        return file_get_contents($fixture);
    }

    /**
     * @param bool $decoded
     *
     * @return \stdClass|string
     */
    public static function getMotTestDataVehicleClass4($decoded = false)
    {
        return self::prepareReturnValue(self::getJsonFromFile('MotTestDataVehicleClass4.json'), $decoded);
    }

    /**
     * @param bool $decoded
     *
     * @return \stdClass|string
     */
    public static function getMotTestDataVehicleClass1($decoded = false)
    {
        return self::prepareReturnValue(self::getJsonFromFile('MotTestDataVehicleClass1.json'), $decoded);
    }

    /**
     * @param bool $decoded
     *
     * @return \stdClass|string
     */
    public static function getDvsaVehicleTestDataVehicleClass4($decoded = false)
    {
        return self::prepareReturnValue(self::getJsonFromFile('DvsaVehicleClass4.json'), $decoded);
    }

    /**
     * @param bool $decoded
     *
     * @return \stdClass|string
     */
    public static function getDvsaVehicleTestDataVehicleClass1($decoded = false)
    {
        return self::prepareReturnValue(self::getJsonFromFile('DvsaVehicleClass1.json'), $decoded);
    }

    /**
     * @param string $content
     * @param bool   $decoded
     *
     * @return \stdClass|string
     */
    private static function prepareReturnValue($content, $decoded)
    {
        return $decoded ? json_decode($content) : $content;
    }
}
