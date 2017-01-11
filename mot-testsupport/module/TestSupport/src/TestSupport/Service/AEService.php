<?php

namespace TestSupport\Service;

use DvsaCommon\Dto\Contact\AddressDto;
use DvsaCommon\Dto\Contact\EmailDto;
use DvsaCommon\Dto\Contact\PhoneDto;
use DvsaCommon\Dto\Organisation\AuthorisedExaminerAuthorisationDto;
use DvsaCommon\Dto\Organisation\OrganisationContactDto;
use DvsaCommon\Dto\Organisation\OrganisationDto;
use DvsaCommon\Enum\CompanyTypeCode;
use DvsaCommon\Enum\OrganisationContactTypeCode;
use DvsaCommon\Enum\PhoneContactTypeCode;
use DvsaCommon\UrlBuilder\AuthorisedExaminerUrlBuilder;
use DvsaCommon\Utility\DtoHydrator;
use TestSupport\Helper\TestSupportRestClientHelper;
use TestSupport\Helper\TestDataResponseHelper;
use TestSupport\Helper\DataGeneratorHelper;
use DvsaCommon\Constants\OrganisationType;
use Doctrine\ORM\Query\ResultSetMapping;
use DvsaCommon\Utility\ArrayUtils;
use Doctrine\ORM\EntityManager;
use Zend\View\Model\JsonModel;

class AEService
{
    const DEFAULT_AREA_OFFICE = 1;

    /**
     * @var TestSupportRestClientHelper
     */
    private $restClientHelper;

    /**
     * @var EntityManager
     */
    private $em;

    public function __construct(
        TestSupportRestClientHelper $restClientHelper,
        EntityManager $em
    ) {
        $this->restClientHelper = $restClientHelper;
        $this->em = $em;
    }

    /**
     * @param mixed $data including optional "diff" string to differentiate testers,
     *                    requestor => {username,password} of DVSA scheme management user with whom to create AE
     *                    optional "slots", to specify the number of slots (default 2000)
     *
     * @return JsonModel ID of new AE
     */
    public function create($data)
    {
        $organisationDto = $this->generateDto($data);
        $result = $this->restClientHelper->getJsonClient($data)->post(
            AuthorisedExaminerUrlBuilder::of()->toString(),
            DtoHydrator::dtoToJson($organisationDto)
        );

        $aeId = $result['data']['id'];
        $this->addSlotsToAe($aeId, ArrayUtils::tryGet($data, 'slots', 2000));

        return TestDataResponseHelper::jsonOk(
            [
                "message" => "Authorised Examiner created",
                "id" => $aeId,
                "aeRef" => $result['data']['aeRef'],
                "aeName" => $organisationDto->getName(),
            ]
        );
    }

    private function generateDto($data)
    {
        $dataGenerator = DataGeneratorHelper::buildForDifferentiator($data);
        $emailAddress = $dataGenerator->emailAddress('org-');
        $aeName = ArrayUtils::tryGet($data, 'organisationName', $dataGenerator->organisationName());

        $address = (new AddressDto())
            ->setAddressLine1($dataGenerator->addressLine1())
            ->setPostcode("IP1 1LL")
            ->setTown("Ipswich");
        $phones = (new PhoneDto())
            ->setIsPrimary(true)
            ->setContactType(PhoneContactTypeCode::BUSINESS)
            ->setNumber($dataGenerator->phoneNumber());
        $email = (new EmailDto())
            ->setIsPrimary(true)
            ->setEmail(ArrayUtils::tryGet($data, 'emailAddress', $emailAddress));

        $contact = (new OrganisationContactDto())
            ->setType(OrganisationContactTypeCode::REGISTERED_COMPANY)
            ->setAddress($address)
            ->setPhones([$phones])
            ->setEmails([$email]);

        $authForAeDto = new AuthorisedExaminerAuthorisationDto();
        $authForAeDto->setAssignedAreaOffice(self::DEFAULT_AREA_OFFICE);

        return (new OrganisationDto())
            ->setName($aeName)
            ->setAuthorisedExaminerAuthorisation($authForAeDto)
            ->setOrganisationType(OrganisationType::AUTHORISED_EXAMINER)
            ->setCompanyType(CompanyTypeCode::SOLE_TRADER)
            ->setContacts([$contact]);
    }

    /**
     * @param int $aeId
     * @param int $slots
     */
    private function addSlotsToAe($aeId, $slots)
    {
        $this->em->getConnection()->executeUpdate(
            "UPDATE organisation SET slots_balance = :slots WHERE id = :id",
            ["slots" => $slots, "id" => $aeId]
        );

        $this->em->flush();
    }

}
