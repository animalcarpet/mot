<?php
declare(strict_types=1);

namespace Dvsa\Mot\Frontend\MotTestModule\Service;

use DvsaCommon\Cache\CacheKeyGenerator;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;

class RfrCacheKeyGenerator implements CacheKeyGenerator, AutoWireableInterface
{
    const DEFAULT_DATE_FORMAT = 'Y-m-d';
    const IS_VE_MARKER = 've';
    const IS_TESTER_MARKER = 'tester';

    /**
     * @param int $vehicleClass
     * @param int $categoryId
     * @param bool $isVe
     * @param \DateTime|null $date
     * @return string
     */
    public static function createFrom(int $vehicleClass, int $categoryId, bool $isVe, \DateTime $date = null): string
    {
        return self::createKey($vehicleClass, $categoryId, $isVe, $date);
    }

    /**
     * @param array ...$args
     * @return string
     */
    private static function createKey(...$args): string
    {
        /** @var \DateTime $date */
        $date = $args[3] instanceOf \DateTime ? ($args[3]) : new \DateTime();
        $userTypeMarker = $args[2] === true ? self::IS_VE_MARKER : self::IS_TESTER_MARKER;

        // vehicleClass_categoryId_userType_DateTime
        return sprintf('%s_%s_%s_%s',
            $args[0],
            $args[1],
            $userTypeMarker,
            $date->format(self::DEFAULT_DATE_FORMAT)
        );
    }
    
    /**
     * @param array $args input arguments
     * @return string caching key
     *
     * @throws \InvalidArgumentException
     */
    public function generateKey(...$args): string
    {
        if(!$this->isInputValid(...$args)){
            throw new \InvalidArgumentException('Invalid input arguments');
        }

        return self::createKey(...$args);
    }

    /**
     * @param array ...$args
     * @return bool
     */
    private function isInputValid(...$args): bool
    {
        if(count($args) <3) return false;

        if(!is_int($args[0])) return false;
        if(!is_int($args[1])) return false;
        if(!is_bool($args[2])) return false;

        if(!empty($args[3]) && !($args[3] instanceof \DateTime)) return false;

        return true;
    }
}