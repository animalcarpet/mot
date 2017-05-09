<?php

namespace DvsaEntities\Repository;

use Doctrine\ORM\Query\AST\FromClause;
use Doctrine\ORM\Query\SqlWalker;

class MotTestIndexSqlWalker extends SqlWalker
{
    const HINT_USE_INDEX = 'MotTestIndexSqlWalker.UseIndex';

    /**
     * @param FromClause $fromClause
     *
     * @return mixed|string
     */
    public function walkFromClause($fromClause)
    {
        $result = parent::walkFromClause($fromClause);

        if ($index = $this->getQuery()->getHint(self::HINT_USE_INDEX)) {
            $result = preg_replace('#(\bFROM\s*\w+\s*\w+)#', '\1 USE INDEX ('.$index.')', $result);
        }

        return $result;
    }
}
