<?php

namespace Dvsa\Mot\Behat\Support\Api;

use Dvsa\Mot\Behat\Support\Request;

class Person extends MotApi
{
    const PATH = 'person/{user_id}';
    const PATH_PERSONAL_DETAILS = 'personal-details/{user_id}';
    const PATH_TESTER = 'tester/{user_id}';

    public function getPersonMotTestingClasses($token, $user_id)
    {
        return $this->client->request(new Request(
            'GET',
            str_replace('{user_id}', $user_id, self::PATH).'/mot-testing',
            ['Content-Type' => 'application/json', 'Authorization' => 'Bearer '.$token]
        ));
    }

    public function getPersonDashboard($token, $user_id)
    {
        return $this->client->request(new Request(
            'GET',
            str_replace('{user_id}', $user_id, self::PATH.'/dashboard'),
            ['Content-Type' => 'application/json', 'Authorization' => 'Bearer '.$token]
        ));
    }

    public function getTesterDetails($token, $user_id)
    {
        return $this->client->request(new Request(
            'GET',
            str_replace('{user_id}', $user_id, self::PATH_TESTER),
            ['Content-Type' => 'application/json', 'Authorization' => 'Bearer '.$token]
        ));
    }

    public function updateUserEmail($token, $user_id, $newEmail, $emailConfirmation = null)
    {
        if (is_null($emailConfirmation)) {
            $emailConfirmation = $newEmail;
        }

        $body = json_encode([
            'title' => 'Mr',
            'firstName' => 'Bob',
            'middleName' => 'Thomas',
            'surname' => 'Arctor',
            'gender' => 'Male',
            'drivingLicenceNumber' => 'GARDN605109C99LY60',
            'drivingLicenceRegion' => 'GB',
            'addressLine1' => 'Straw Hut',
            'addressLine2' => '5 Uncanny St',
            'addressLine3' => '',
            'town' => 'Liverpool',
            'postcode' => 'L1 1PQ',
            'email' => $newEmail,
            'emailConfirmation' => $emailConfirmation,
            'phoneNumber' => '+768-45-4433630',
            'update-profile' => 'update-profile',
            'dateOfBirth' => '1981-04-24',
        ]);

        return $this->client->request(new Request(
            'PUT',
            str_replace('{user_id}', $user_id, self::PATH_PERSONAL_DETAILS),
            ['Content-Type' => 'application/json', 'Authorization' => 'Bearer '.$token],
            $body
        ));
    }
}
