<?php

namespace Dvsa\Mot\Behat\Support\Api;

use Dvsa\Mot\Behat\Support\Request;

class CustomerService extends MotApi
{
    const PATH = 'search-person?';
    const PATH_PROFILE = 'person/{userId}/help-desk-profile-unrestricted';
    const PATH_UPDATE_LICENCE = 'person/{userId}/driving-licence';

    public function search($token, $searchData)
    {
        $searchString = '';
        //If the Search Criteria is Empty Do not Include in the GET Request
        if (!empty($searchData['userName'])) {
            $searchString = $searchString.'username='.urlencode($searchData['userName']);
        }

        if (!empty($searchData['firstName'])) {
            //If First Search Param Include &
            $append = empty($searchString) ? '' : '&';
            $searchString = $searchString.$append.'firstName='.urlencode($searchData['firstName']);
        }

        if (!empty($searchData['lastName'])) {
            $append = empty($searchString) ? '' : '&';
            $searchString = $searchString.$append.'lastName='.urlencode($searchData['lastName']);
        }

        if (!empty($searchData['email'])) {
            $append = empty($searchString) ? '' : '&';
            $searchString = $searchString.$append.'email='.urlencode($searchData['email']);
        }

        if (!empty($searchData['postCode'])) {
            $append = empty($searchString) ? '' : '&';
            $searchString = $searchString.$append.'postcode='.urlencode($searchData['postCode']);
        }

        if (!empty($searchData['dateOfBirth'])) {
            $append = empty($searchString) ? '' : '&';
            $searchString = $searchString.$append.'dateOfBirth='.urlencode($searchData['dateOfBirth']);
        }

        return $this->sendGetRequest(
            $token,
            self::PATH.$searchString
        );
    }

    public function helpDeskProfile($token, $userId)
    {
        return $this->sendGetRequest(
            $token,
            str_replace('{userId}', $userId, self::PATH_PROFILE)
        );
    }

    public function updateLicence($token, $userId, $licenceDetails)
    {
        return $this->sendPostRequest(
            $token,
            str_replace('{userId}', $userId, self::PATH_UPDATE_LICENCE),
            $licenceDetails
        );
    }

    public function deleteLicence($token, $userId)
    {
        return $this->sendDeleteRequest(
            $token,
            str_replace('{userId}', $userId, self::PATH_UPDATE_LICENCE)
        );
    }
}
