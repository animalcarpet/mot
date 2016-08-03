<?php

namespace DvsaCommon\Configuration;

/**
 * Should not be used on it's own, please use MotConfig.
 * Extracts data from a config array. Will return default value if it does not exist.
 *
 * Class DefaultConfigExtractor
 * @package DvsaCommon\Configuration
 */
class DefaultConfigExtractor
{
    private $configExtractor;
    private $default;

    public function __construct(array $config, $default)
    {
        $this->default = $default;
        $this->configExtractor = new ConfigExtractor($config);
    }

    /**
     * @return mixed
     */
    public function get()
    {
        $keys = func_get_args();

        if (!$this->configExtractor->keysExist($keys)){
            return $this->default;
        }

        return $this->configExtractor->extractValue($keys);
    }
}
