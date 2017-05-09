<?php

namespace TestSupport\Service;

use TestSupport\Helper\TestDataResponseHelper;

/**
 * FeaturesService.
 */
class FeaturesService
{
    /**
     * @var array
     */
    private $config;

    /**
     * FeaturesService constructor.
     *
     * @param $configFile
     */
    public function __construct($configFile)
    {
        if (!is_readable($configFile)) {
            throw new \InvalidArgumentException(sprintf('Could not read the feature toggle config: "%s"', $configFile));
        }

        $this->config = require_once $configFile;
    }

    /**
     * @param $feature
     *
     * @return \Zend\View\Model\JsonModel
     */
    public function get($feature)
    {
        if (!isset($this->config['feature_toggle'][$feature])) {
            throw new \InvalidArgumentException(sprintf('Feature toggle "%s" is not available in the features toggle config',
                $feature));
        }

        $value = (bool) $this->config['feature_toggle'][$feature];

        return TestDataResponseHelper::jsonOk(['toggle' => $value]);
    }
}
