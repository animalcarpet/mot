<?php

namespace DvsaCommon\ApiClient\MotTest\DuplicateCertificate\Dto;

use DvsaCommon\DtoSerialization\ReflectiveDtoInterface;

class MotTestDuplicateCertificateEditAllowedDto implements ReflectiveDtoInterface
{
    private $editAllowed;
    private $isAllowedToEditAllCertificates;

    /**
     * @return bool
     */
    public function getEditAllowed()
    {
        return $this->editAllowed;
    }

    /**
     * @param bool $editAllowed
     * @return $this
     */
    public function setEditAllowed($editAllowed)
    {
        $this->editAllowed = $editAllowed;
        return $this;
    }

    /**
     * @return bool
     */
    public function getIsAllowedToEditAllCertificates()
    {
        return $this->isAllowedToEditAllCertificates;
    }

    /**
     * @param bool $isAllowedToEditAllCertificates
     * @return $this
     */
    public function setIsAllowedToEditAllCertificates($isAllowedToEditAllCertificates)
    {
        $this->isAllowedToEditAllCertificates = $isAllowedToEditAllCertificates;
        return $this;
    }
}