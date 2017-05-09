<?php

namespace DvsaEntities\DqlBuilder;

use Doctrine\DBAL\Driver\Statement;

/**
 * @deprecated use Doctrine
 */
class NativeQueryBuilder
{
    const JOIN_TYPE_INNER = 'INNER';
    const JOIN_TYPE_LEFT = 'LEFT';
    const JOIN_TYPE_RIGHT = 'RIGHT';

    protected $select = [];
    protected $from = [];
    protected $join = [];
    protected $params = [];
    protected $where = [];
    protected $orderBy = [];
    protected $offset = null;
    protected $limit = null;

    /**
     * Return sql query.
     *
     * @return string
     */
    public function getSql()
    {
        $parts = [
            'SELECT', implode(', ', $this->select),
            'FROM', implode(', ', $this->from),
        ];

        if (!empty($this->join)) {
            $parts[] = implode(' ', $this->join);
        }

        if (!empty($this->where)) {
            array_push($parts, 'WHERE 1=1', implode(' ', $this->where));
        }

        if (!empty($this->orderBy)) {
            array_push($parts, 'ORDER BY', implode(', ', $this->orderBy));
        }

        if (isset($this->limit) && $this->limit > 0) {
            array_push($parts, 'LIMIT', (string) $this->limit);

            if (isset($this->offset) && $this->offset > 0) {
                array_push($parts, 'OFFSET', $this->offset);
            }
        }

        return implode(' ', $parts);
    }

    /**
     * @param Statement $sql
     *
     * @return Statement
     */
    public function bindParametersToStatement(Statement &$sql)
    {
        foreach ($this->getParameters() as $name => $value) {
            $sql->bindValue($name, $value);
        }

        return $sql;
    }
    /**
     * Set parameters for binding.
     *
     * @param array $params
     *
     * @return $this
     */
    public function setParameters(array $params)
    {
        $this->params = $params;

        return $this;
    }

    public function getParameters()
    {
        return $this->params;
    }

    /**
     * Set parameter for binding.
     *
     * @param string $paramName parameter name
     * @param mixed  $value     bind value
     *
     * @return $this
     */
    public function setParameter($paramName, $value)
    {
        if ($value instanceof \DateTime) {
            $value = $value->format('Y-m-d H:i:s');
        }

        $this->params[':'.$paramName] = $value;

        return $this;
    }

    /**
     * Add part of SELECT statement.
     *
     * @param string $select part of Select statement
     * @param string $key    key of part
     *
     * @return $this
     */
    public function select($select, $key = null)
    {
        if ($key === null) {
            $this->select[] = $select;
        } else {
            $this->select[$key] = $select;
        }

        return $this;
    }

    /**
     * Add part of FROM statement.
     *
     * @param string $tableName add table to From statement
     * @param string $alias     table alias and key of part
     *
     * @return $this
     */
    public function from($tableName, $alias = null)
    {
        $this->from[$alias ?: $tableName] = $tableName.($alias ? ' AS '.$alias : '');

        return $this;
    }

    /**
     * Add join to FROM statement.
     *
     * @param string $tableName jointed table
     * @param string $alias     table alias and key of part
     * @param string $condition join condition (ON statement)
     * @param string $type      type of Join
     *
     * @return $this
     */
    public function join($tableName, $alias, $condition, $type = self::JOIN_TYPE_INNER)
    {
        $this->join[$alias ?: $tableName] = implode(
            ' ', array_filter(
                [
                    $type,
                    'JOIN',
                    $tableName,
                    ($alias ? 'AS '.$alias : null),
                    'ON',
                    $condition,
                ]
            )
        );

        return $this;
    }

    /**
     * Add condition (part) to WHERE statement.
     *
     * @param string $condition condition
     * @param string $key       key of part
     *
     * @return $this
     */
    public function andWhere($condition, $key = null)
    {
        if ($key === null) {
            $this->where[] = 'AND '.$condition;
        } else {
            $this->where[$key] = 'AND '.$condition;
        }

        return $this;
    }

    /**
     * Add part of ORDER BY statement.
     *
     * @param string $condition sort column and order type
     * @param string $key       key of part
     *
     * @return $this
     */
    public function orderBy($condition, $key = null)
    {
        if ($key === null) {
            $this->orderBy[] = $condition;
        } else {
            $this->orderBy[$key] = $condition;
        }

        return $this;
    }

    /**
     * @param int $limit
     *
     * @return $this
     */
    public function setLimit($limit)
    {
        $this->limit = (int) $limit;

        return $this;
    }

    /**
     * @param int $offset
     *
     * @return $this
     */
    public function setOffset($offset)
    {
        $this->offset = (int) $offset;

        return $this;
    }

    /**
     * Reset(remove) part of statement by specified key.
     *
     * @param string $part Part of statement ('select', 'from', 'join', 'where', 'orderby')
     * @param string $key  Key of removed part
     *
     * @return $this
     */
    public function resetPart($part, $key = null)
    {
        if ($key !== null) {
            unset($this->{$part}[$key]);
        } else {
            unset($this->{$part});
        }

        return $this;
    }
}
