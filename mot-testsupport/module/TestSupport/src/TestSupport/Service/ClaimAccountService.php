<?php

namespace TestSupport\Service;

use DvsaCommon\Dto\Contact\AddressDto;
use DvsaCommon\Dto\Contact\EmailDto;
use DvsaCommon\Dto\Contact\PhoneDto;
use DvsaCommon\Dto\Organisation\AuthorisedExaminerAuthorisationDto;
use DvsaCommon\Dto\Organisation\OrganisationContactDto;
use DvsaCommon\Dto\Organisation\OrganisationDto;
use DvsaCommon\Enum\AuthorisationForAuthorisedExaminerStatusCode;
use DvsaCommon\Enum\CompanyTypeCode;
use DvsaCommon\Enum\OrganisationContactTypeCode;
use DvsaCommon\Enum\PhoneContactTypeCode;
use DvsaCommon\UrlBuilder\AuthorisedExaminerUrlBuilder;
use DvsaCommon\Utility\DtoHydrator;
use TestSupport\FieldValidation;
use TestSupport\Helper\TestSupportRestClientHelper;
use TestSupport\Helper\TestDataResponseHelper;
use TestSupport\Helper\DataGeneratorHelper;
use DvsaCommon\Constants\OrganisationType;
use Doctrine\ORM\Query\ResultSetMapping;
use DvsaCommon\Utility\ArrayUtils;
use Doctrine\ORM\EntityManager;
use Zend\View\Model\JsonModel;

class ClaimAccountService
{

    /**
     * @var EntityManager
     */
    private $em;

    public function __construct(
        EntityManager $em
    ) {
        $this->em = $em;
    }

    /**
     * @param mixed $data including "personId" key
     *
     * @return empty model
     */
    public function create($data)
    {
        FieldValidation::checkForRequiredFieldsInData(['personId'], $data);
        $this->setClaimAccountRequired($data['personId']);
        return [];
    }


    private function setClaimAccountRequired($personId)
    {
        $this->em->getConnection()->executeUpdate(
            "UPDATE person SET is_account_claim_required = 1 WHERE id = :id",
            ["id" => $personId]
        );

        $this->em->flush();
    }

}
