<?php

namespace UserAdmin\Service;

use DvsaClient\Mapper\UserAdminMapper;
use DvsaCommon\Auth\MotAuthorisationServiceInterface;
use DvsaCommon\Auth\PermissionInSystem;
use DvsaCommon\Dto\Person\PersonContactDto;
use DvsaCommon\Dto\Person\PersonHelpDeskProfileDto;
use DvsaCommon\Enum\MessageTypeCode;
use DvsaCommon\HttpRestJson\Exception\NotFoundException;

/**
 * Service for account management by helpdesk.
 */
class HelpdeskAccountAdminService
{
    /** @var UserAdminMapper */
    private $userAdminMapper;

    /** @var MotAuthorisationServiceInterface */
    private $authorisationService;

    /**
     * @param MotAuthorisationServiceInterface $authorisationService
     * @param UserAdminMapper                  $userAdminMapper
     */
    public function __construct(
        MotAuthorisationServiceInterface $authorisationService,
        UserAdminMapper $userAdminMapper
    ) {
        $this->authorisationService = $authorisationService;
        $this->userAdminMapper = $userAdminMapper;
    }

    /**
     * @param int $personId
     *
     * @return PersonHelpDeskProfileDto
     *
     * @throws NotFoundException
     */
    public function getUserProfile($personId)
    {
        return $this->userAdminMapper->getUserProfile($personId);
    }

    public function resetAccount($personId)
    {
        $this->authorisationService->assertGranted(PermissionInSystem::CREATE_MESSAGE_FOR_OTHER_USER);

        $this->postMessage(
            [
                'personId' => $personId,
                'messageTypeCode' => MessageTypeCode::ACCOUNT_RESET_BY_LETTER,
            ]
        );
    }

    /**
     * @param int $personId
     *
     * @return bool
     */
    public function resetClaimAccount($personId)
    {
        return $this->userAdminMapper->resetClaimAccount($personId);
    }

    /**
     * @param array $params
     *
     * @return bool
     */
    public function postMessage($params)
    {
        return $this->userAdminMapper->postMessage($params);
    }

    /**
     * @param $personId
     * @param $email
     *
     * @return PersonContactDto
     */
    public function updatePersonContactEmail($personId, $email)
    {
        return $this->userAdminMapper->updateEmail($personId, $email);
    }

    /**
     * @param int    $personId
     * @param string $licenceNumber
     * @param string $licenceRegion
     *
     * @return mixed|string
     */
    public function updateDrivingLicence($personId, $licenceNumber, $licenceRegion)
    {
        return $this->userAdminMapper->updateDrivingLicence($personId, $licenceNumber, $licenceRegion);
    }

    /**
     * @param $personId
     *
     * @return mixed|string
     */
    public function deleteDrivingLicence($personId)
    {
        return $this->userAdminMapper->deleteDrivingLicence($personId);
    }

    /**
     * @param $personId
     * @param $firstName
     * @param $middleName
     * @param $lastName
     *
     * @return string|\Exception
     */
    public function updateName($personId, $firstName, $middleName, $lastName)
    {
        return $this->userAdminMapper->updatePersonName($personId, $firstName, $middleName, $lastName);
    }

    public function updateDateOfBirth($personId, $data)
    {
        return $this->userAdminMapper->updateDateOfBirth($personId, $data);
    }

    public function updateAddress($personId, $firstLine, $secondLine, $thirdLine, $townOrCity, $country, $postcode)
    {
        return $this->userAdminMapper->updatePersonAddress($personId, $firstLine, $secondLine, $thirdLine, $townOrCity, $country, $postcode);
    }

    /**
     * @param $personId
     * @param $newPhoneNumber
     */
    public function editTelephoneNumber($personId, $newPhoneNumber)
    {
        $this->userAdminMapper->updatePersonTelephoneNumber($personId, $newPhoneNumber);
    }
}
