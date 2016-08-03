<?php
namespace DvsaCommon\ApiClient\Statistics\ComponentFailRate\Dto;

use DvsaCommon\DtoSerialization\ReflectiveDtoInterface;

class ComponentDto implements ReflectiveDtoInterface
{
    /** @var int */
    private $id;
    /** @var  string */
    private $name;
    /** @var  double */
    private $percentageFailed;

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    public function getPercentageFailed()
    {
        return $this->percentageFailed;
    }

    public function setPercentageFailed($percentageFailed)
    {
        $this->percentageFailed = $percentageFailed;
        return $this;
    }
}