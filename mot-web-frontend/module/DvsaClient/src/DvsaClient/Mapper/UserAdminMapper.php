<?php

namespace DvsaClient\Mapper;

use Application\Data\ApiPersonalDetails;
use Dvsa\Mot\Frontend\PersonModule\Controller\ChangeTelephoneController;
use DvsaCommon\Dto\Person\PersonContactDto;
use DvsaCommon\Dto\Person\PersonHelpDeskProfileDto;
use DvsaCommon\Dto\Person\SearchPersonResultDto;
use DvsaCommon\Dto\Security\SecurityQuestionDto;
use DvsaCommon\HttpRestJson\Exception\RestApplicationException;
use DvsaCommon\UrlBuilder\MessageUrlBuilder;
use DvsaCommon\UrlBuilder\PersonUrlBuilder;
use DvsaCommon\UrlBuilder\UserAdminUrlBuilder;
use DvsaCommon\HttpRestJson\Client;

/**
 * Class UserAdminMapper
 *
 * @package DvsaClient\Mapper
 */
class UserAdminMapper extends DtoMapper
{
    const NEW_PROFILE_URL = '/personal-details/{:id}/';

    /** @var ApiPersonalDetails */
    private $personDetailsService;

    /**
     * UserAdminMapper constructor.
     * @param Client             $client
     */
    public function __construct(
        Client $client
    )
    {
        parent::__construct($client);
        $this->personDetailsService = new ApiPersonalDetails($client);
    }

    /**
     * @param int       $personId
     * @param int       $questionId
     *
     * @return SecurityQuestionDto
     */
    public function getSecurityQuestion($questionId, $personId)
    {
        $url = UserAdminUrlBuilder::securityQuestionGet($questionId, $personId)->toString();
        return $this->get($url);
    }

    /**
     * @param int       $personId
     * @param int       $questionId
     * @param string    $answer
     *
     * @return bool
     */
    public function checkSecurityQuestion($questionId, $personId, $answer)
    {
        $url = UserAdminUrlBuilder::securityQuestionCheck($questionId, $personId)->toString();
        return $this->getWithParams($url, $answer);
    }

    /**
     * @param int    $personId
     * @return PersonHelpDeskProfileDto
     */
    public function getUserProfile($personId)
    {
        $response = $this->personDetailsService->getPersonalDetailsData($personId);
        return PersonHelpDeskProfileDto::fromArray($response);
    }

    /**
     * @param $criteria
     * @return \DvsaCommon\Dto\Person\SearchPersonResultDto[]
     */
    public function searchUsers($criteria)
    {
        $url = PersonUrlBuilder::personSearch()->queryParams($criteria)->toString();
        $response = $this->client->get($url);

        return SearchPersonResultDto::getList($response['data']);
    }

    /**
     * @param $personId
     * @return bool
     */
    public function resetClaimAccount($personId)
    {
        $url = PersonUrlBuilder::resetClaimAccount($personId)->toString();
        return $this->client->get($url);
    }

    /**
     * @param $params
     * @return bool
     */
    public function postMessage($params)
    {
        $url = MessageUrlBuilder::message()->toString();
        return $this->client->post($url, $params);
    }

    /**
     * @param integer $personId
     * @param string $email
     * @throws RestApplicationException
     * @return PersonContactDto
     */
    public function updateEmail($personId, $email)
    {
        $url = UserAdminUrlBuilder::personContact($personId);
        return $this->client->patch($url, ["email" => $email]);
    }

    /**
     * @param $personId
     * @param $licenceNumber
     * @param $licenceRegion
     * @return mixed|string
     */
    public function updateDrivingLicence($personId, $licenceNumber, $licenceRegion)
    {
        $url = UserAdminUrlBuilder::licenceDetails($personId);

        return $this->client->post(
            $url, [
                "drivingLicenceNumber" => $licenceNumber,
                "drivingLicenceRegion" => $licenceRegion,
            ]
        );
    }

    /**
     * @param int $personId
     * @return mixed|string
     */
    public function deleteDrivingLicence($personId)
    {
        $url = UserAdminUrlBuilder::licenceDetails($personId);

        return $this->client->delete($url);
    }

    /**
     * @param $personId
     * @param $firstName
     * @param $middleName
     * @param $lastName
     * @return mixed|string
     */
    public function updatePersonName($personId, $firstName, $middleName, $lastName)
    {
        $url = UserAdminUrlBuilder::personName($personId);

        return $this->client->post(
            $url, [
                "firstName" => $firstName,
                "middleName" => $middleName,
                "lastName" => $lastName,
            ]
        );
    }

    public function updatePersonAddress($personId, $firstLine, $secondLine, $thirdLine, $townOrCity, $country, $postcode)
    {
        $url = UserAdminUrlBuilder::personAddress($personId);

        return $this->client->post(
            $url, [
                "firstLine" => $firstLine,
                "secondLine" => $secondLine,
                "thirdLine" => $thirdLine,
                "townOrCity" => $townOrCity,
                "country" => $country,
                "postcode" => $postcode,
            ]
        );
    }

    /**
     * @param $personId
     * @param array $data
     * @return mixed|string
     */
    public function updateDateOfBirth($personId, array $data)
    {
        $url = UserAdminUrlBuilder::personDayOfBirth($personId);

        return $this->client->post($url, $data);
    }

    /**
     * @param $personId
     * @param $newPhoneNumber
     * @return mixed|string
     */
    public function updatePersonTelephoneNumber($personId, $newPhoneNumber)
    {
        $url = UserAdminUrlBuilder::personTelephone($personId);

        $this->client->put(
            $url, [
                ChangeTelephoneController::PHONE_NUMBER_KEY => $newPhoneNumber
            ]
        );
    }
}
