<?php
namespace Dvsa\Mot\Behat\Support\Data\Collection;

class SharedDataCollection
{
    /**
     * @var DataCollection[]
     */
    private static $collection = [];

    public static function get($class)
    {
        if (array_key_exists($class, static::$collection)) {
            return static::$collection[$class];
        }

        $dataCollection = new DataCollection($class);
        static::$collection[$dataCollection->getExpectedInstance()] = $dataCollection;

        return $dataCollection;
    }

    public static function set(DataCollection $dataCollection)
    {
        static::$collection[$dataCollection->getExpectedInstance()] = $dataCollection;
    }

    public static function clear()
    {
        foreach (static::$collection as $dataCollection) {
            $dataCollection->clear();
        }
    }
}
