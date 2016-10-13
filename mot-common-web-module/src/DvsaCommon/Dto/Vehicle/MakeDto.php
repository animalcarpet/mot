<?php

namespace DvsaCommon\Dto\Vehicle;

use DvsaCommon\Dto\AbstractDataTransferObject;
use DvsaCommon\DtoSerialization\ReflectiveDtoInterface;

/**
 * Class MakeDto
 *
 * @package DvsaCommon\Dto\Vehicle
 */
class MakeDto extends AbstractDataTransferObject implements ReflectiveDtoInterface
{
    /** @var int */
    private $id;
    /** @var string */
    private $name;
    /** @var string */
    private $code;

    /**
     * @param int $id
     *
     * @return MakeDto
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $code
     *
     * @return MakeDto
     */
    public function setCode($code)
    {
        $this->code = $code;

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
     * @param string $name
     *
     * @return MakeDto
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
}
