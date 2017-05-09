<?php

namespace Core\Catalog\BusinessRole;

use DvsaCommon\Utility\TypeCheck;

class BusinessRole
{
    const ORGANISATION_TYPE = 'organisation';
    const SITE_TYPE = 'site';

    private $code;
    private $name;
    private $type;

    public function __construct($code, $name, $type)
    {
        $this->code = $code;
        $this->name = $name;

        TypeCheck::assertInArray($type, [self::ORGANISATION_TYPE, self::SITE_TYPE]);
        $this->type = $type;
    }

    public function getCode()
    {
        return $this->code;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getType()
    {
        return $this->type;
    }
}
