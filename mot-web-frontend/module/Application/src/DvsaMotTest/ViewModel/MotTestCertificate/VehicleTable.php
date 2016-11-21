<?php

namespace DvsaMotTest\ViewModel\MotTestCertificate;

class VehicleTable
{
    const SINGLE_OLDER_TEST_TEXT = "1 older test";
    const MANY_OLDER_TESTS_TEXT = "%s older tests";

    private $registration;
    private $vin;
    private $make;
    private $model;
    private $firstTest;
    private $olderTests;
    private $olderTestsCount = 0;
    private $index = 0;

    /**
     * @param string $registration
     */
    public function setRegistration($registration)
    {
        $this->registration = $registration;
    }

    /**
     * @return string
     */
    public function getRegistration()
    {
        return $this->registration;
    }

    /**
     * @return string
     */
    public function getVin()
    {
        return $this->vin;
    }

    /**
     * @param string $vin
     */
    public function setVin($vin)
    {
        $this->vin = $vin;
    }

    /**
     * @return string
     */
    public function getMakeAndModel()
    {
        return sprintf("%s, %s", $this->make, $this->model);
    }

    /**
     * @param string $make
     */
    public function setMake($make)
    {
        $this->make = $make;
    }

    public function getMake()
    {
        return $this->make;
    }

    /**
     * @param string $model
     */
    public function setModel($model)
    {
        $this->model = $model;
    }

    public function getModel()
    {
        return $this->model;
    }

    /**
     * @param MotTestCertificateTableItem $test
     */
    public function addRow(MotTestCertificateTableItem $test)
    {
        if ($this->firstTest == null) {
            $this->firstTest = $test;
        } else {
            $this->olderTests[] = $test;
            $this->olderTestsCount++;
        }
    }

    /**
     * @return MotTestCertificateTableItem[]
     */
    public function getOlderTests()
    {
        return $this->olderTests;
    }

    public function getAllTests()
    {
        yield $this->firstTest;

        foreach ($this->olderTests as $olderTest) {
            yield $olderTest;
        }
    }

    public function getTotalTestCount()
    {
        return ($this->firstTest ? 1 : 0) + $this->olderTestsCount;
    }

    public function areThereOlderTests()
    {
        return $this->olderTestsCount > 0;
    }

    public function getOlderTestsSplitText()
    {
        return $this->olderTestsCount == 1 ?
            self::SINGLE_OLDER_TEST_TEXT
            : sprintf(self::MANY_OLDER_TESTS_TEXT, $this->olderTestsCount);
    }

    /**
     * @return MotTestCertificateTableItem
     */
    public function getFirstTest()
    {
        return $this->firstTest;
    }

    /**
     * @return int
     */
    public function getIndex()
    {
        return $this->index;
    }

    /**
     * @return string
     */
    public function getToggleTableClassName()
    {
        return 'toggle-table-' . $this->getIndex();
    }

    /**
     * @param int $index
     */
    public function setIndex($index)
    {
        $this->index = $index;
    }
}