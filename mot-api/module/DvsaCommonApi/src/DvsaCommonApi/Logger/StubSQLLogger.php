<?php

namespace DvsaCommonApi\Logger;

use Doctrine\DBAL\Logging\SQLLogger;
use Zend\Log\LoggerInterface;

class StubSQLLogger implements SQLLogger
{
    /**
     * Logs a SQL statement somewhere.
     *
     * @param string     $sql    The SQL to be executed.
     * @param array|null $params The SQL parameters.
     * @param array|null $types  The SQL parameter types.
     *
     * @return void
     */
    public function startQuery($sql, array $params = null, array $types = null)
    {
    }

    /**
     * Marks the last started query as stopped. This can be used for timing of queries.
     *
     * @return void
     */
    public function stopQuery()
    {
    }
}
