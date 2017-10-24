<?php
declare(strict_types=1);

namespace Dvsa\Mot\Frontend\MotTestModule\Service;

use DvsaCommon\Cache\Cache;
use DvsaCommon\Cache\CacheKeyGenerator;
use DvsaCommon\Constants\FeatureToggle;
use DvsaFeature\FeatureToggles;
use Zend\Cache\Storage\StorageInterface;
use Zend\Cache\Exception\ExceptionInterface;

class RfrCache extends Cache
{
    /**
     * @var FeatureToggles
     */
    private $featureToggles;

    /**
     * @param StorageInterface $storage
     * @param CacheKeyGenerator $cacheKeyGenerator
     * @param FeatureToggles $featureToggles
     */
    public function __construct(
        StorageInterface $storage,
        CacheKeyGenerator $cacheKeyGenerator,
        FeatureToggles $featureToggles
    )
    {
        parent::__construct($storage, $cacheKeyGenerator);

        $this->featureToggles = $featureToggles;
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
}