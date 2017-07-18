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
use TestSupport\Helper\TestSupportRestClientHelper;
use TestSupport\Helper\TestDataResponseHelper;
use TestSupport\Helper\DataGeneratorHelper;
use DvsaCommon\Constants\OrganisationType;
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
        if (!array_key_exists('areaOfficeSiteNumber', $data)) {
            $data['areaOfficeSiteNumber'] = self::DEFAULT_AREA_OFFICE;
        }

        $organisationDto = $this->generateDto($data);

        $result = $this->restClientHelper->getJsonClient($data)->post(
            AuthorisedExaminerUrlBuilder::of()->toString(),
            DtoHydrator::dtoToJson($organisationDto)
        );

        $aeId = $result['data']['id'];
        $this->setSiteAeStatusApproved($result['data']['aeRef']);
        $this->addSlotsToAe($aeId, ArrayUtils::tryGet($data, 'slots', 2000));

        return TestDataResponseHelper::jsonOk(
            [
                'message' => 'Authorised Examiner created',
                'id' => $aeId,
                'aeRef' => $result['data']['aeRef'],
                'aeName' => $organisationDto->getName(),
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
            ->setPostcode('IP1 1LL')
            ->setTown('Ipswich');
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

        $correspondanceContact = (new OrganisationContactDto())
            ->setType(OrganisationContactTypeCode::CORRESPONDENCE)
            ->setAddress($address)
            ->setPhones([$phones])
            ->setEmails([$email]);

        $authForAeDto = new AuthorisedExaminerAuthorisationDto();
        $authForAeDto->setAssignedAreaOffice($data['areaOfficeSiteNumber']);

        return (new OrganisationDto())
            ->setName($aeName)
            ->setAuthorisedExaminerAuthorisation($authForAeDto)
            ->setOrganisationType(OrganisationType::AUTHORISED_EXAMINER)
            ->setSlotBalance(ArrayUtils::tryGet($data, 'slots', 1001))
            ->setCompanyType(CompanyTypeCode::SOLE_TRADER)
            ->setContacts([$contact, $correspondanceContact]);
    }

    /**
     * @param int $aeId
     * @param int $slots
     */
    private function addSlotsToAe($aeId, $slots)
    {
        $this->em->getConnection()->executeUpdate(
            'UPDATE organisation SET slots_balance = :slots WHERE id = :id',
            ['slots' => $slots, 'id' => $aeId]
        );

        $this->em->flush();
    }

    /**
     * For a site we create, this will ensure the AE that manages it has status approved
     * so that a link site-to-ae request will succeed.
     *
     * @param string $aeNumber
     */
    public function setSiteAeStatusApproved($aeNumber)
    {
        $this->em->getConnection()->executeUpdate(
            '
            UPDATE auth_for_ae
            SET
                status_id = (SELECT id FROM auth_for_ae_status WHERE code=:STATUS)
            WHERE
                ae_ref=:AE_NUMBER
            ',
            [
                'STATUS' => AuthorisationForAuthorisedExaminerStatusCode::APPROVED,
                'AE_NUMBER' => $aeNumber,
            ]
        );

        $this->em->flush();
    }

    public function getSlotBalanceForAE($aeId)
    {
        $result = $this->em->getConnection()->executeQuery(
            'SELECT slots_balance FROM organisation WHERE id=:organisation_id',
            ['organisation_id' => $aeId]
        )->fetch();

        return $result['slots_balance'];
    }

    public function getLinkId($aeId, $siteId)
    {
        $result = $this->em->getConnection()->executeQuery(
            'SELECT id FROM organisation_site_map WHERE organisation_id = :organisation_id AND site_id = :site_id AND end_date IS NULL',
            ['organisation_id' => $aeId, 'site_id' => $siteId]
        )->fetch();

        return $result['id'];
    }
}
