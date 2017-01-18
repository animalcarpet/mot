<?php

namespace DvsaEntities\DqlBuilder\SearchParam;

class MotTestLogSearchParam extends MotTestSearchParam
{
    const DEFAULT_SORT_COLUMN = 'mt.id';

    private static $sortCriteria
        = [
            'testDateTime' => 'testDate',
            'vehicleVRM'   => 'registration',
            'makeModel'    => ['makeName', 'modelName'],
            'tester'       => ['userName', 'siteNumber'],
            'statusType'   => ['status', 'testTypeName'],
        ];

    /**
     * @return string
     */
    public function getSortColumnNameDatabase()
    {
        $sortBy = $this->getSortColumnId();

        if (isset(self::$sortCriteria[$sortBy])) {
            return self::$sortCriteria[$sortBy];
        }

        return self::DEFAULT_SORT_COLUMN;
    }
}
