<?php

namespace Dvsa\Mot\Frontend\SecurityCardModule\CardOrder\ViewModel;

use Dvsa\Mot\Frontend\SecurityCardModule\CardOrder\Form\SecurityCardAddressForm;
use Zend\Form\Form;

class CardOrderAddressViewModel
{
    /**
     * @var SecurityCardAddressForm $form
     */
    private $form;

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
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;
    }

    public function getForm()
    {
        return $this->form;
    }

    public function setForm(Form $form)
    {
        $this->form = $form;
    }
}