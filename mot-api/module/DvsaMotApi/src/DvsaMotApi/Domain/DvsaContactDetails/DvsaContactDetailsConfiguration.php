<?php
/**
 * This file is part of the DVSA MOT API project.
 *
 * @link https://gitlab.motdev.org.uk/mot/mot
 */

namespace DvsaMotApi\Domain\DvsaContactDetails;

/**
 * Class DvsaContactDetailsConfiguration.
 */
class DvsaContactDetailsConfiguration
{
    const KEY__NAME = 'name';
    const KEY__PHONE = 'phone';
    const DEFAULT__NAME = 'Driver Vehicles & Standards Agency';
    const DEFAULT__PHONE = '03001239000';

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $phone;

    /**
     * DvsaContactDetailsConfiguration constructor.
     *
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->name = isset($config[self::KEY__NAME]) ?
            $config[self::KEY__NAME] : self::DEFAULT__NAME;

        $this->phone = isset($config[self::KEY__PHONE]) ?
            $config[self::KEY__PHONE] : self::DEFAULT__PHONE;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getPhone()
    {
        return $this->phone;
    }
}
