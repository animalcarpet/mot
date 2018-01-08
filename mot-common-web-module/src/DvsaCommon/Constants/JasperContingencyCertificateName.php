<?php

namespace DvsaCommon\Constants;

class JasperContingencyCertificateName
{
    const CT20 = 'CT20';
    const CT30 = 'CT30';
    const EU_CT20 = 'EU_CT20';
    const EU_CT30 = 'EU_CT30';
    const CT32 = 'CT32';

    public static function getAll()
    {
        return [
            self::CT20,
            self::CT30,
            self::EU_CT20,
            self::EU_CT30,
            self::CT32,
        ];
    }
}
