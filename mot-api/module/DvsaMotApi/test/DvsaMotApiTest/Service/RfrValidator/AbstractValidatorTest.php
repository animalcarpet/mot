<?php

namespace DvsaMotApiTest\Service\RfrValidator;

use PHPUnit_Framework_TestCase;

/**
 * Class AbstractValidatorTest.
 */
abstract class AbstractValidatorTest extends PHPUnit_Framework_TestCase
{
    protected $debug = false;

    protected $validRfrIds = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 2001, 2002, 2003, 2004, 2005, 2006,
                              2007, 2008, 2009, 2010, 2011, 2012, 2013, 2015, 2016, 2017, 2018, 2019, 2020, 2021, 2027, 2028,
                              2029, 2030, 2031, 2032, 2033, 2034, 2035, 2036, 2037, 2038, 2039, 2040, 2041, 2042, 2043, 2044,
                              2045, 2046, 2047, 2048, 2049, 2050, 2051, 2052, 2053, 2054, 2055, 2056, 2057, 2058, 2059, 2060,
                              2061, 2062, 2063, 2064, 2065, 2066, 2067, 2068, 2069, 2070, 2071, 2072, 2073, 2074, 2075, 2076,
                              2077, 2078, 2079, 2080, 2081, 2082, 2083, 2084, 2085, 2086, 2087, 2088, 2089, 2090, 2091, 2092,
                              2093, 2094, 2095, 2096, 2097, ];
    //todo: these are placeholder ids for now, and not mapped Rfr ids.
    protected $validMappedRfrIds = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 2001, 2002, 2003, 2004, 2005, 2006,
                                    2007, 2008, 2009, 2010, 2011, 2012, 2013, 2015, 2016, 2017, 2018, 2019, 2020, 2021, 2027, 2028,
                                    2029, 2030, 2031, 2032, 2033, 2034, 2035, 2036, 2037, 2038, 2039, 2040, 2041, 2042, 2043, 2044,
                                    2045, 2046, 2047, 2048, 2049, 2050, 2051, 2052, 2053, 2054, 2055, 2056, 2057, 2058, 2059, 2060,
                                    2061, 2062, 2063, 2064, 2065, 2066, 2067, 2068, 2069, 2070, 2071, 2072, 2073, 2074, 2075, 2076,
                                    2077, 2078, 2079, 2080, 2081, 2082, 2083, 2084, 2085, 2086, 2087, 2088, 2089, 2090, 2091, 2092,
                                    2093, 2094, 2095, 2096, 2097, ];

    /**
     * Returns a validator for the test.
     *
     * @param $mappedRfrId
     * @param $fixture
     *
     * @return mixed
     */
    abstract protected function getValidator($mappedRfrId, $fixture);

    /**
     * Returns the names of the story under test.
     *
     * @return array
     */
    abstract public function getFixtureName();

    /**
     * Returns the fixtures for the test.
     *
     * @return array
     */
    abstract public function getFixtures();

    /**
     * The template pattern for testing a validator, do not override in subclasses
     * All validators will be tested the same, the only thing that changes is
     * the fixtures in getFixtures and the validator in getValidator.
     */
    public function testValidate()
    {
        foreach ($this->getFixtures() as $mappedRfrId => $fixture) {
            $validator = $this->getValidator($mappedRfrId, $fixture);
            $validationPassed = $validator->validate();

            $msg = "mappedRfrId/rfrId: {$mappedRfrId}/".
                    "{$fixture['rfrId']}, ".
                    'values: '.str_replace(';', "\n", serialize($fixture));

            if ($this->debug === true) {
                $name = $this->getFixtureName();
                echo "{$name}: ".
                    "{$fixture['rfrId']}, ".
                    "{$fixture['score']}, ".
                    "{$fixture['decision']}, ".
                    "{$fixture['category']}, ".
                    "{$fixture['error']}, \n";
            }

            if ($fixture['error'] === 1) {
                $this->assertFalse($validationPassed, $msg);
                $error = $validator->getError();
                $this->assertInstanceOf(\DvsaCommonApi\Error\Message::class, $error, $msg);
                $this->assertEquals($error->message, $fixture['message'], $msg);
                $this->assertArrayHasKey(
                    $mappedRfrId,
                    $error->fieldDataStructure['mappedRfrs'],
                    $msg
                );
                $this->assertArrayHasKey(
                    $fixture['failedItem'],
                    $error->fieldDataStructure['mappedRfrs'][$mappedRfrId],
                    $msg
                );
            } else {
                $this->assertTrue($validationPassed, $msg);
            }
        }
    }

    /**
     * @param bool $debug
     *
     * @return AbstractValidatorTest
     */
    public function setDebug($debug)
    {
        $this->debug = $debug;

        return $this;
    }

    /**
     * @return bool
     */
    public function getDebug()
    {
        return $this->debug;
    }
/*
    public function getFixturesAsKeysArray()
    {
        $output = array();
        $fixtures = $this->getFixtures();
        foreach ($fixtures as $fixture) {
            $output[$fixture['rfrId']] = $fixture;
        }
        return $output;
    }
*/
    /*
    public function getFixturesAsJson()
    {
        return json_encode($this->getFixturesAsKeysArray());
    }
    */
}
