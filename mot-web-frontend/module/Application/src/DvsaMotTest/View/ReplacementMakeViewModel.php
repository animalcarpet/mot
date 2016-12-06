<?php

namespace DvsaMotTest\View;

use DvsaCommon\Utility\ArrayUtils;

class ReplacementMakeViewModel
{
    private $id;
    private $name;

    public function __construct(array $data)
    {
        $this->id = ArrayUtils::tryGet($data, 'id');
        $this->name = ArrayUtils::tryGet($data, 'name');
    }

    /**
     * @param $id
     * @return $this
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
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
}
