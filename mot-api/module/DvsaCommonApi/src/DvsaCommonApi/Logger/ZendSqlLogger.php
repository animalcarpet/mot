<?php

namespace DvsaCommonApi\Logger;

use Doctrine\DBAL\Logging\SQLLogger;

/**
 * Class ZendSqlLogger.
 *
 * Uses the injected Zend Framework 2 logger to log Doctrine 2 SQL.
 */
class ZendSqlLogger implements SQLLogger
{
    private $zf2Logger;

    public function __construct($zf2Logger)
    {
        $this->zf2Logger = $zf2Logger;
    }

    /**
     * {@inheritdoc}
     */
    public function startQuery($sql, array $params = null, array $types = null)
    {
        $this->zf2Logger->debug('SQL ['.$sql.']');

        // TODO Need to avoid performance hit if logging disabled
        if ($params) {
            $this->zf2Logger->debug('params ['.print_r($params, true).']');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function stopQuery()
    {
    }
}
