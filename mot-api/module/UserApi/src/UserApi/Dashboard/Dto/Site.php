<?php

namespace UserApi\Dashboard\Dto;

/**
 * Vts representation.
 */
class Site
{
    /** @var $id int */
    private $id;

    /** @var $name string */
    private $name;

    /** @var $siteNumber string */
    private $siteNumber;

    /** @var $positions array */
    private $positions;

    public function __construct(\DvsaEntities\Entity\Site $site, $positions)
    {
        $this->setId($site->getId());
        $this->setName($site->getName());
        $this->setSiteNumber($site->getSiteNumber());
        $this->setPositions($positions);
    }

    /**
     * @param \DvsaEntities\Entity\Site[] $sites
     *
     * @return Site[]
     */
    public static function getList($sites)
    {
        $result = [];

        /** @var $site \DvsaEntities\Entity\Site */
        foreach ($sites as $site) {
            $result[] = new self($site, []);
        }

        return $result;
    }

    /**
     * Array representation of this object.
     *
     * @return array
     */
    public function toArray()
    {
        $roles = [];

        /** @var $positionAtSite \DvsaEntities\Entity\SiteBusinessRoleMap */
        foreach ($this->getPositions() as $positionAtSite) {
            $roles[] = $positionAtSite->getSiteBusinessRole()->getCode();
        }

        return [
            'id' => $this->getId(),
            'name' => $this->getName(),
            'siteNumber' => $this->getSiteNumber(),
            'positions' => $roles,
        ];
    }

    /**
     * @param int $id
     *
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
     * @param string $name
     *
     * @return $this
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
     * @return $this
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
