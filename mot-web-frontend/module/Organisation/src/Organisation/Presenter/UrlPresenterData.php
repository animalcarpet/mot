<?php

namespace Organisation\Presenter;

class UrlPresenterData
{
    private $root;
    private $value;

    /** @var array[] */
    private $params;

    /** @var array[] */
    private $queryParams;

    private $id;

    public function __construct($value, $root, array $params, array $queryParams = null, $id = null)
    {
        $this->value = $value;
        $this->root = $root;
        $this->params = $params;
        $this->queryParams = $queryParams;
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getRoot()
    {
        return $this->root;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * @return array
     */
    public function getQueryParams()
    {
        return $this->queryParams;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }
}