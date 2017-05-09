<?php

namespace Organisation\Presenter;

use DvsaCommon\Dto\Organisation\OrganisationDto;
use DvsaCommon\Enum\CompanyTypeName;
use DvsaCommon\UrlBuilder\AuthorisedExaminerUrlBuilderWeb;
use DvsaCommon\Utility\AddressUtils;

class AuthorisedExaminerPresenter
{
    /**
     * @var OrganisationDto
     */
    private $organisation;

    public function __construct($organisation)
    {
        $this->organisation = $organisation;
    }

    public function getSlotsBalance()
    {
        return $this->organisation->getSlotBalance();
    }

    public function getName()
    {
        return $this->organisation->getName();
    }

    public function getNumber()
    {
        return $this->organisation->getAuthorisedExaminerAuthorisation()->getAuthorisedExaminerRef();
    }

    public function getTradingName()
    {
        return $this->organisation->getTradingAs();
    }

    public function getAddressInline()
    {
        return AddressUtils::stringify($this->organisation->getRegisteredCompanyContactDetail()->getAddress());
    }
    public function getCompanyNumber()
    {
        return $this->organisation->getRegisteredCompanyNumber();
    }

    public function getOrganisationType()
    {
        return $this->organisation->getOrganisationType();
    }

    public function isBusinessTypeCompany()
    {
        return $this->organisation->getCompanyType() == CompanyTypeName::COMPANY;
    }

    public function getCompanyType()
    {
        return $this->organisation->getCompanyType();
    }

    public function getAssignRoleUrl()
    {
        return AuthorisedExaminerUrlBuilderWeb::roles($this->organisation->getId());
    }

    public function getPrincipalsUrl()
    {
        return AuthorisedExaminerUrlBuilderWeb::principals($this->organisation->getId());
    }
}
