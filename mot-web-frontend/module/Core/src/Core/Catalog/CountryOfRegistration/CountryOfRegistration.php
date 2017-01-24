<?php


namespace Core\Catalog\CountryOfRegistration;


class CountryOfRegistration
{
    private $code;
    private $name;

    function __construct($code, $name)
    {
        $this->code = $code;
        $this->name = $name;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getCode()
    {
        return $this->code;
    }

    public function getName()
    {
        return $this->name;
    }
}