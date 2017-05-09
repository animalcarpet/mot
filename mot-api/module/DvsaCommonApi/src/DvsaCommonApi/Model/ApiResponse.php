<?php

namespace DvsaCommonApi\Model;

use DvsaCommon\Utility\DtoHydrator;
use Zend\Http\PhpEnvironment\Response;
use Zend\View\Model\JsonModel;

/**
 * Responsible for unifying response format across API layer.
 */
class ApiResponse
{
    /**
     * @param mixed $data
     *
     * @return JsonModel
     */
    public static function jsonOk($data = [])
    {
        return new JsonModel(['data' => DtoHydrator::dtoToJson($data)]);
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

    /**
     * @param $status
     *
     * @return Response
     */
    public static function httpResponse($status)
    {
        $response = new Response();

        return $response->setStatusCode($status);
    }
}
