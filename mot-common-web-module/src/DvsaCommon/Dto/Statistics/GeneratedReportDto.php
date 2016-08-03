<?php

namespace DvsaCommon\Dto\Statistics;

use DvsaCommon\DtoSerialization\ReflectiveDtoInterface;

class GeneratedReportDto implements ReflectiveDtoInterface
{
    private $name;

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }
}
