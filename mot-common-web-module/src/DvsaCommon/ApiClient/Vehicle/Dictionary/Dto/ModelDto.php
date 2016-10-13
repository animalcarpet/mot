<?php
namespace DvsaCommon\ApiClient\Vehicle\Dictionary\Dto;

use DvsaCommon\DtoSerialization\ReflectiveDtoInterface;

class ModelDto implements ReflectiveDtoInterface
{
    private $id;
    private $code;
    private $name;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return ModelDto
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param string $code
     * @return ModelDto
     */
    public function setCode($code)
    {
        $this->code = $code;
        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return ModelDto
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }
}
