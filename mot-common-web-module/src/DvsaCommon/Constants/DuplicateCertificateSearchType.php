<?php

namespace DvsaCommon\Constants;

class DuplicateCertificateSearchType
{
    const SEARCH_TYPE_VIN = 'vin';
    const SEARCH_TYPE_VRM = 'vrm';

    public static function getAll()
    {
        return [
            self::SEARCH_TYPE_VIN,
            self::SEARCH_TYPE_VRM,
        ];
    }
}
