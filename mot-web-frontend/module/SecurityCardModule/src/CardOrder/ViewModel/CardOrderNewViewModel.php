<?php

namespace Dvsa\Mot\Frontend\SecurityCardModule\CardOrder\ViewModel;


class CardOrderNewViewModel
{
    private $hasAnActiveCard;

    private $userId;

    /**
     * @return mixed
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * @param mixed $userId
     * @return CardOrderNewViewModel
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getHasAnActiveCard()
    {
        return $this->hasAnActiveCard;
    }

    /**
     * @param mixed $hasAnActiveCard
     * @return CardOrderNewViewModel
     */
    public function setHasAnActiveCard($hasAnActiveCard)
    {
        $this->hasAnActiveCard = $hasAnActiveCard;
        return $this;
    }

}