<?php
namespace Dvsa\Mot\Behat\Support\Data\DefaultData;

use DvsaCommon\Dto\Vehicle\ModelDto;

class DefaultModel
{
    private static $model;

    public static function set(ModelDto $model)
    {
        self::$model = $model;
    }

    /**
     * @return ModelDto
     */
    public static function get()
    {
        return self::$model;
    }
}
