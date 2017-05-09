<?php

namespace DvsaElasticSearch\Model;

use DvsaCommon\Utility\ArrayUtils;

/**
 * I wrap an array containing the ES configuration and provide some useful helper
 * functions for the application code.
 *
 * Class ESConfig
 */
class ESConfig
{
    const SITE = 'site';
    const MOT = 'mot';
    const VEHICLE = 'vehicle';
    const REBUILD = 'rebuild';
    const RESET_LOCK = 'resetlock';

    protected $config;

    /**
     * @param array $data The loaded configuration file
     *
     * @throws \Exception
     */
    public function __construct(array $data)
    {
        foreach (['indices', 'passphrase', 'client'] as $key) {
            if (!array_key_exists($key, $data)) {
                throw new \Exception('ESConfig: missing mandatory key: '.$key);
            }
        }
        $this->config = $data;
    }

    /**
     * Returns the ElasticSearch cluster Url we use for communications.
     *
     * @return string
     *
     * @throws \Exception
     */
    public function esHostUrl()
    {
        if (!array_key_exists('host', $this->config['client'])) {
            throw new \Exception('ESConfig: missing client.host key');
        }

        return $this->config['client']['host'];
    }

    /**
     * Gets the hostname and timeout to be used when "pinging" the remote
     * cluster to see if it is alive and responding.
     */
    public function getPingInfo()
    {
        return [
            'host' => $this->esHostUrl(),
            'timeout' => ArrayUtils::tryGet($this->config['client'], 'timeout', 1),
        ];
    }

    /**
     * @param string $type one of the document types
     *
     * @throws \Exception
     *
     * @return string the index to use for this type
     */
    public function indexForType($type)
    {
        if (array_key_exists($type, $this->config['indices'])) {
            return $this->config['indices'][$type]['index'];
        }
        throw new \Exception('Unhandled document type: '.$type);
    }

    /**
     * @param string $type one of the document types
     *
     * @throws \Exception
     *
     * @return string the ES document type name to use for this type
     */
    public function docTypeForType($type)
    {
        if (array_key_exists($type, $this->config['indices'])) {
            return $this->config['indices'][$type]['type'];
        }
        throw new \Exception('Unhandled document type: '.$type);
    }

    /**
     * Find the required passphrase. Throws if not present.
     *
     * @param $action
     *
     * @throws \Exception
     *
     * @return string configured value
     */
    public function passPhrase($action)
    {
        if (array_key_exists($action, $this->config['passphrase'])) {
            return $this->config['passphrase'][$action];
        }

        throw new \Exception('Missing passphrase for: '.$action);
    }

    /**
     * Helper: answers true of the passphrase is correct for the given action
     * All other variations are replied to with a false value.
     * If the $action is not present an exception will be raised.
     *
     * @param $action
     * @param $value
     *
     * @return bool
     */
    public function validPassPhrase($action, $value)
    {
        return $value == $this->passPhrase($action);
    }

    /**
     * Helper: obtains a general top-level key or the default value.
     *
     * @param $key
     * @param $default
     */
    public function getKeyOr($key, $default)
    {
        if (array_key_exists($key, $this->config)) {
            return $this->config[$key];
        }

        return $default;
    }
}
