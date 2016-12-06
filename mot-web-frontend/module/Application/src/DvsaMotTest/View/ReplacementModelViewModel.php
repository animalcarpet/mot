<?php

namespace DvsaMotTest\View;

use DvsaCommon\Utility\ArrayUtils;

class ReplacementModelViewModel
{

    /** @var string $id */
    private $id;
    /** @var string $name */
    private $name;

    public function __construct(array $data)
    {
        $this->id = ArrayUtils::tryGet($data, 'id');
        $this->name = ArrayUtils::tryGet($data, 'name');
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
