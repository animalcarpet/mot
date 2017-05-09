<?php

namespace Dashboard\Model;

use DvsaCommon\Utility\ArrayUtils;

/**
 * Data about AE link to person.
 */
class AuthorisedExaminer
{
    /** @var $id int */
    private $id;

    /** @var $reference string */
    private $reference;

    /** @var $name string */
    private $name;

    /** @var $tradingAs string */
    private $tradingAs;

    /** @var $managerId int */
    private $managerId;

    /** @var $slots int */
    private $slots;

    /** @var $slotsWarnings int */
    private $slotsWarnings;

    /** @var $sites Site[] */
    private $sites;

    /** @var $position string */
    private $position;

    public function __construct($data)
    {
        $this->setId(ArrayUtils::get($data, 'id'));
        $this->setManagerId(ArrayUtils::get($data, 'managerId'));
        $this->setReference(ArrayUtils::get($data, 'reference'));
        $this->setName(ArrayUtils::get($data, 'name'));
        $this->setTradingAs(ArrayUtils::get($data, 'tradingAs'));
        $this->setSlots(ArrayUtils::get($data, 'slots'));
        $this->setSlotsWarnings(ArrayUtils::get($data, 'slotsWarnings'));
        $this->setSites(Site::getList(ArrayUtils::get($data, 'sites')));
        $this->setPosition(ArrayUtils::get($data, 'position'));
    }

    /**
     * @return int
     */
    public function getSiteCount()
    {
        return count($this->getSites());
    }

    /**
     * Converts array of hash arrays to array of AuthorisedExaminer objects.
     *
     * @param array $data
     *
     * @return AuthorisedExaminer[]
     */
    public static function getList(array $data = [])
    {
        $result = [];

        foreach ($data as $ae) {
            $result[] = new self($ae);
        }

        return $result;
    }

    /**
     * @param int $aedm
     *
     * @return AuthorisedExaminer
     */
    public function setManagerId($aedm)
    {
        $this->managerId = $aedm;

        return $this;
    }

    /**
     * @return int
     */
    public function getManagerId()
    {
        return $this->managerId;
    }

    /**
     * @param int $id
     *
     * @return AuthorisedExaminer
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
     * @return AuthorisedExaminer
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
     * @param string $reference
     *
     * @return AuthorisedExaminer
     */
    public function setReference($reference)
    {
        $this->reference = $reference;

        return $this;
    }

    /**
     * @return string
     */
    public function getReference()
    {
        return $this->reference;
    }

    /**
     * @param Site[] $sites
     *
     * @return AuthorisedExaminer
     */
    public function setSites($sites)
    {
        $this->sites = $sites;

        return $this;
    }

    /**
     * @return Site[]
     */
    public function getSites()
    {
        return $this->sites;
    }

    /**
     * @param int $slots
     *
     * @return AuthorisedExaminer
     */
    public function setSlots($slots)
    {
        $this->slots = $slots;

        return $this;
    }

    /**
     * @return int
     */
    public function getSlots()
    {
        return $this->slots;
    }

    /**
     * @param int $slotsWarnings
     *
     * @return AuthorisedExaminer
     */
    public function setSlotsWarnings($slotsWarnings)
    {
        $this->slotsWarnings = $slotsWarnings;

        return $this;
    }

    /**
     * @return int
     */
    public function getSlotsWarnings()
    {
        return $this->slotsWarnings;
    }

    /**
     * @param string $tradingAs
     *
     * @return AuthorisedExaminer
     */
    public function setTradingAs($tradingAs)
    {
        $this->tradingAs = $tradingAs;

        return $this;
    }

    /**
     * @return string
     */
    public function getTradingAs()
    {
        return $this->tradingAs;
    }

    /**
     * @param string $position
     *
     * @return AuthorisedExaminer
     */
    public function setPosition($position)
    {
        $this->position = $position;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getPosition()
    {
        return $this->position;
    }
}
