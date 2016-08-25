<?php

namespace Dvsa\Mot\Frontend\SecurityCardModule\CardOrder\ViewModel;

class CardOrderReviewViewModel
{
    private $addressLineOne;

    private $addressLineTwo;

    private $addressLineThree;

    private $town;

    private $postcode;

    private $name;

    private $userId;

    private $vtsName;

    /**
     * @return mixed
     */
    public function getVtsName()
    {
        return $this->vtsName;
    }

    /**
     * @param mixed $vtsName
     * @return CardOrderReviewViewModel
     */
    public function setVtsName($vtsName)
    {
        $this->vtsName = $vtsName;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getAddressLineOne()
    {
        return $this->addressLineOne;
    }

    /**
     * @param mixed $addressLineOne
     * @return CardOrderAddressViewModel
     */
    public function setAddressLineOne($addressLineOne)
    {
        $this->addressLineOne = $addressLineOne;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getAddressLineTwo()
    {
        return $this->addressLineTwo;
    }

    /**
     * @param mixed $addressLineTwo
     * @return CardOrderAddressViewModel
     */
    public function setAddressLineTwo($addressLineTwo)
    {
        $this->addressLineTwo = $addressLineTwo;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getAddressLineThree()
    {
        return $this->addressLineThree;
    }

    /**
     * @param mixed $addressLineThree
     * @return CardOrderAddressViewModel
     */
    public function setAddressLineThree($addressLineThree)
    {
        $this->addressLineThree = $addressLineThree;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getTown()
    {
        return $this->town;
    }

    /**
     * @param mixed $town
     * @return CardOrderAddressViewModel
     */
    public function setTown($town)
    {
        $this->town = $town;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPostcode()
    {
        return $this->postcode;
    }

    /**
     * @param mixed $postcode
     * @return CardOrderAddressViewModel
     */
    public function setPostcode($postcode)
    {
        $this->postcode = $postcode;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     * @return CardOrderAddressViewModel
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * @param mixed $userId
     * @return CardOrderReviewViewModel
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;
        return $this;
    }
}