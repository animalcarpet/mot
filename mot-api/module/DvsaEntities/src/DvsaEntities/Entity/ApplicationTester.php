<?php

namespace DvsaEntities\Entity;

use Doctrine\ORM\Mapping as ORM;
use DvsaEntities\EntityTrait\CommonIdentityTrait;

/**
 * ApplicationTester.
 *
 * @ORM\Table(name="application_tester")
 * @ORM\Entity
 */
class ApplicationTester
{
    use CommonIdentityTrait;

    /**
     * @var bool
     *
     * @ORM\Column(name="testing_two_wheeled", type="boolean", nullable=true)
     */
    private $testingTwoWheeled = '0';

    /**
     * @var bool
     *
     * @ORM\Column(name="testing_four_wheeled", type="boolean", nullable=true)
     */
    private $testingFourWheeled = '0';

    /**
     * Set testingTwoWheeled.
     *
     * @param bool $testingTwoWheeled
     *
     * @return ApplicationTester
     */
    public function setTestingTwoWheeled($testingTwoWheeled)
    {
        $this->testingTwoWheeled = $testingTwoWheeled;

        return $this;
    }

    /**
     * Get testingTwoWheeled.
     *
     * @return bool
     */
    public function getTestingTwoWheeled()
    {
        return $this->testingTwoWheeled;
    }

    /**
     * Set testingFourWheeled.
     *
     * @param bool $testingFourWheeled
     *
     * @return ApplicationTester
     */
    public function setTestingFourWheeled($testingFourWheeled)
    {
        $this->testingFourWheeled = $testingFourWheeled;

        return $this;
    }

    /**
     * Get testingFourWheeled.
     *
     * @return bool
     */
    public function getTestingFourWheeled()
    {
        return $this->testingFourWheeled;
    }
}
