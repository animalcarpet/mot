<?php

namespace Dashboard\Model;

use DvsaCommon\Utility\ArrayUtils;

/**
 * Data about site (VTS) link to person.
 */
class Site
{
    /** @var $id int */
    private $id;

    /** @var $id string */
    private $name;

    /** @var $id string */
    private $siteNumber;

    /** @var $positions array */
    private $positions;

    public function __construct($data)
    {
        $this->setId(ArrayUtils::get($data, 'id'));
        $this->setName(ArrayUtils::get($data, 'name'));
        $this->setSiteNumber(ArrayUtils::get($data, 'siteNumber'));
        $this->setPositions(ArrayUtils::get($data, 'positions'));
    }

    /**
     * Converts array of hash arrays to array of AuthorisedExaminer objects.
     *
     * @param array $data
     *
     * @return Site[]
     */
    public static function getList($data = [])
    {
        $result = [];
        foreach ($data as $ae) {
            $result[] = new self($ae);
        }

        return $result;
    }

    /**
     * @param int $id
     *
     * @return Site
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
     * @param string $name
     *
     * @return Site
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

    /**
     * @param string $siteNumber
     *
     * @return Site
     */
    public function setSiteNumber($siteNumber)
    {
        $this->siteNumber = $siteNumber;

        return $this;
    }

    /**
     * @return string
     */
    public function getSiteNumber()
    {
        return $this->siteNumber;
    }

    /**
     * @param array $positions
     *
     * @return Site
     */
    public function setPositions($positions)
    {
        $this->positions = $positions;

        return $this;
    }

    /**
     * @return array
     */
    public function getPositions()
    {
        return $this->positions;
    }
}
