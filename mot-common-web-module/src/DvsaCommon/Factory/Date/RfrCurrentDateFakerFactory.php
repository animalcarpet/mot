<?php

namespace DvsaCommon\Factory\Date;

use DvsaCommon\Configuration\MotConfig;
use DvsaCommon\Date\DateTimeHolder;
use DvsaCommon\Date\RfrCurrentDateFaker;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class RfrCurrentDateFakerFactory implements FactoryInterface
{
    const DATE_CONFIG_KEY = 'rfr_fake_current_date';

    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /** @var MotConfig $config */
        $config = $serviceLocator->get(MotConfig::class);
        $fakeDate = $this->getFakeDateValue($config);

        return new RfrCurrentDateFaker(
            new DateTimeHolder(),
            $fakeDate
        );
    }

    /**
     * @param $config
     * @return \DateTime|null
     */
    public function getFakeDateValue($config)
    {
        $fakeDate = $config
            ->withDefault(null)
            ->get(self::DATE_CONFIG_KEY);

        if(null === $fakeDate || !is_string($fakeDate) || empty(trim($fakeDate))) return null;

        try {
            $fakeDate = new \DateTime($fakeDate);
        } catch (\Exception $e) {
            $fakeDate = null;
        }

        return $fakeDate;
    }
}