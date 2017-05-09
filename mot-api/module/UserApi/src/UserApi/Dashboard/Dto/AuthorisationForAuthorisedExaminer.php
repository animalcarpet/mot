<?php

namespace UserApi\Dashboard\Dto;

use DvsaEntities\Entity\AuthorisationForAuthorisedExaminer as AuthorisationForAuthorisedExaminerEntity;

/**
 * AuthorisationForAuthorisedExaminer data returned from API to web.
 */
class AuthorisationForAuthorisedExaminer
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

    public function __construct(
        AuthorisationForAuthorisedExaminerEntity $authorisedExaminer,
        $managerId,
        $vtsList,
        $position,
        $personId
    ) {
        $organisation = $authorisedExaminer->getOrganisation();

        $this->setId($organisation->getId());
        $this->setReference($authorisedExaminer->getNumber());
        $this->setName($organisation->getName());
        $this->setTradingAs($organisation->getTradingAs());
        $this->setManagerId($managerId);
        $this->setSlots($organisation->getSlotBalance());
        $this->setSlotsWarnings($organisation->getSlotsWarning());
        $this->setPosition($position);
        $this->setSites($vtsList);
    }

    public function toArray()
    {
        $sites = [];
        /** @var $site Site */
        foreach ($this->getSites() as $site) {
            $sites[] = $site->toArray();
        }

        return [
            'id' => $this->getId(),
            'reference' => $this->getReference(),
            'name' => $this->getName(),
            'tradingAs' => $this->getTradingAs(),
            'managerId' => $this->getManagerId(),
            'slots' => $this->getSlots(),
            'slotsWarnings' => $this->getSlotsWarnings(),
            'sites' => $sites,
            'position' => $this->getPosition(),
        ];
    }

    /**
     * @param int $aedmId
     *
     * @return AuthorisationForAuthorisedExaminer
     */
    public function setManagerId($aedmId)
    {
        $this->managerId = intval($aedmId);

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
     * @return AuthorisationForAuthorisedExaminer
     */
    public function setId($id)
    {
        $this->id = intval($id);

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
     * @return AuthorisationForAuthorisedExaminer
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
     * @return AuthorisationForAuthorisedExaminer
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
     * @param string $sites
     *
     * @return AuthorisationForAuthorisedExaminer
     */
    public function setSites($sites)
    {
        $this->sites = $sites;

        return $this;
    }

    /**
     * @param Site $site
     *
     * @return bool
     */
    public function hasSite(Site $site)
    {
        /** @var Site $currentSites */
        foreach ($this->getSites() as $currentSites) {
            if ($site->getId() == $currentSites->getId()) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array
     */
    public function getSites()
    {
        return $this->sites;
    }

    /**
     * @param string $slots
     *
     * @return AuthorisationForAuthorisedExaminer
     */
    public function setSlots($slots)
    {
        $this->slots = $slots;

        return $this;
    }

    /**
     * @return string
     */
    public function getSlots()
    {
        return $this->slots;
    }

    /**
     * @param string $slotsWarnings
     *
     * @return AuthorisationForAuthorisedExaminer
     */
    public function setSlotsWarnings($slotsWarnings)
    {
        $this->slotsWarnings = intval($slotsWarnings);

        return $this;
    }

    /**
     * @return string
     */
    public function getSlotsWarnings()
    {
        return $this->slotsWarnings;
    }

    /**
     * @param string $tradingAs
     *
     * @return AuthorisationForAuthorisedExaminer
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
     * @return AuthorisationForAuthorisedExaminer
     */
    public function setPosition($position)
    {
        $this->position = $position;

        return $this;
    }

    /**
     * @return string
     */
    public function getPosition()
    {
        return $this->position;
    }
}
