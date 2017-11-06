<?php
declare(strict_types=1);

namespace Dvsa\Mot\Frontend\MotTestModule\Service;

use DvsaCommon\Cache\Cache;
use DvsaCommon\Cache\CacheKeyGenerator;
use DvsaCommon\Constants\FeatureToggle;
use DvsaFeature\FeatureToggles;
use Zend\Cache\Storage\StorageInterface;
use Zend\Cache\Exception\ExceptionInterface;
use DvsaApplicationLogger\Log\Logger;

class RfrCache extends Cache
{
    /**
     * @var FeatureToggles
     */
    private $featureToggles;
    /**
     * @var Logger
     */
    private $logger;

    /**
     * @param StorageInterface $storage
     * @param CacheKeyGenerator $cacheKeyGenerator
     * @param FeatureToggles $featureToggles
     * @param Logger $logger
     */
    public function __construct(
        StorageInterface $storage,
        CacheKeyGenerator $cacheKeyGenerator,
        FeatureToggles $featureToggles,
        Logger $logger
    )
    {
        parent::__construct($storage, $cacheKeyGenerator);

        $this->featureToggles = $featureToggles;
        $this->logger = $logger;
    }

    /**
     * @param int $vehicleClass
     * @param int $categoryId
     * @param bool $isVe
     * @param \DateTime|null $date
     *
     * @return array|null
     */
    public function getItem(int $vehicleClass, int $categoryId, bool $isVe, \DateTime $date = null)
    {
        try {
            $key = $this->generateKey($vehicleClass, $categoryId, $isVe, $date);
            return  $this->storage->getItem($key);

        } catch (\Exception $ex) {
            $this->logMsg("Failed to get from cache, Message: {$ex->getMessage()} , stackTrace: {$ex->getTraceAsString()}", func_get_args(), __METHOD__);
            return null;
        }
    }

    /**
     * @param int $vehicleClass
     * @param int $categoryId
     * @param bool $isVe
     * @param \DateTime|null $date
     * @param mixed $value
     *
     * @return bool
     */
    public function setItem(int $vehicleClass, int $categoryId, bool $isVe, \DateTime $date = null, $value): bool
    {
        try {
            $key = $this->generateKey($vehicleClass, $categoryId, $isVe, $date);
            return $this->storage->setItem($key, $value);

        } catch (\Exception $ex) {
            $this->logMsg("Failed to set item in cache, Message: {$ex->getMessage()} , stackTrace: {$ex->getTraceAsString()}", func_get_args(), __METHOD__);
            return false;
        }
    }

    /**
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->featureToggles->isEnabled(FeatureToggle::RFR_CACHE);
    }

    /**
     * @param $msg
     * @param $methodArgs
     * @param string $method
     */
    private function logMsg($msg, $methodArgs, $method = __METHOD__)
    {
        $methodArgsPrintable = $this->printifyFunctionArgs($methodArgs);

        $loggedMsg = sprintf("[%s] (function args: %s) %s",
            $method,
            print_r($methodArgsPrintable, true),
            $msg
        );

        $this->logger->warn($loggedMsg);
    }

    /**
     * @param $methodArgs
     * @return array
     */
    private function printifyFunctionArgs($methodArgs)
    {
        $return = [];

        if(is_null($methodArgs)){
            return [ 0 =>'null'];
        }

        foreach($methodArgs as $key => $value)
        {
            if (is_null($value)) {
                $return[$key] = 'null';
                continue;
            }

            if (is_bool($value)) {
                $return[$key] = true === $value ? 'true' : 'false';
                continue;
            }

            if (is_array($value)) {
                $count = count($value);
                $return[$key] = "array of $count elements";
                continue;
            }

            $return[$key] = $value;
        }

        return $return;
    }
}