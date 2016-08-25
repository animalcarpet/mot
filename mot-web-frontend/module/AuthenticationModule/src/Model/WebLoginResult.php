<?php

namespace Dvsa\Mot\Frontend\AuthenticationModule\Model;

use Dvsa\Mot\Frontend\AuthenticationModule\Model\WebLogingResult2FaPageEnum;

class WebLoginResult
{
    /** @var  string */
    private $code;

    /** @var  int */
    private $twoFaPage;

    private $isSecondFactorRequired = false;

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param string $code
     * @return WebLoginResult
     */
    public function setCode($code)
    {
        $this->code = $code;
        return $this;
    }

    /**
     * @return int
     */
    public function getTwoFaPage()
    {
        return $this->twoFaPage;
    }

    /**
     * @param int $twoFaPage
     */
    public function setTwoFaPage($twoFaPage)
    {
        $this->twoFaPage = $twoFaPage;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isSecondFactorRequired()
    {
        return $this->isSecondFactorRequired;
    }

    /**
     * @param boolean $isSecondFactorRequired
     * @return WebLoginResult
     */
    public function setSecondFactorRequired($isSecondFactorRequired)
    {
        $this->isSecondFactorRequired = $isSecondFactorRequired;
        return $this;
    }
}