<?php

namespace Dvsa\Mot\Behat\Support\Api;

class VehicleDictionary extends MotApi
{
    const PATH_MAKE_LIST = '/vehicle-dictionary/make';
    const PATH_MODEL_LIST = '/vehicle-dictionary/make/{make_id}/models';

    public function getMakeList($token)
    {
        return $this->sendRequest($token, self::METHOD_GET, self::PATH_MAKE_LIST);
    }

    public function getModelListByMakeId($token, $makeId)
    {
        $path = str_replace("{make_id}", $makeId, self::PATH_MODEL_LIST);
        return $this->sendRequest($token, self::METHOD_GET, $path);
    }
}
