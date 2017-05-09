<?php

namespace TestSupport\Helper;

use Zend\View\Model\JsonModel;

/**
 * Originally copied from \TestSupport\TestDataResponseHelper.
 */
class TestDataResponseHelper
{
    /**
     * @param mixed $data
     *
     * @return JsonModel
     */
    public static function jsonOk($data = [])
    {
        return new JsonModel(['data' => $data]);
    }

    /**
     * @param $data
     *
     * @return JsonModel
     */
    public static function jsonError($data)
    {
        return new JsonModel(['errors' => $data]);
    }
}
