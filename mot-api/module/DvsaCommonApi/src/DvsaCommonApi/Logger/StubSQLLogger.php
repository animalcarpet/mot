<?php

namespace DvsaCommonApi\Logger;

use Doctrine\DBAL\Logging\SQLLogger;

class StubSQLLogger implements SQLLogger
{
    /**
     * Logs a SQL statement somewhere.
     *
     * @param string     $sql    The SQL to be executed
     * @param array|null $params The SQL parameters
     * @param array|null $types  The SQL parameter types
     */
    public function startQuery($sql, array $params = null, array $types = null)
    {
    }

    /**
     * Marks the last started query as stopped. This can be used for timing of queries.
     */
    public function stopQuery()
    {
    }
}
