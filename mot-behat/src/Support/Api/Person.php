<?php

namespace Dvsa\Mot\Behat\Support\Api;

class Person extends MotApi
{
    const PATH = 'person/{user_id}';
    const PATH_PERSONAL_DETAILS = 'personal-details/{user_id}';
    const PATH_ROLES = '/roles';
    const PATH_ROLES_ROLE = '/roles/{role}';
    const PATH_DASHBOARD = '/dashboard';
    const PATH_STATS = '/stats';
    const PATH_RBAC_ROLES = '/rbac-roles';
    const PATH_PASSWORD = '/password';
    const PATH_NAME = '/name';
    const PATH_ADDRESS = '/address';
    const PATH_DATE_OF_BIRTH = '/date-of-birth';
    const PATH_LICENCE_UPDATE = '/driving-licence';
    const PATH_TELEPHONE_NUMBER = '/phone-number';
    const PATH_RESET = '/reset-password';
    const PATH_RESET_VALIDATE_TOKEN = '/{token}';
    const PATH_RESET_CHANGE_PASSWORD_WITH_TOKEN = '/account/password-change';
    const PATH_PASSWORD_EXPIRY_NOTIFICATION = '/password-expiry-notification';
    const PATH_EMAIL = '/contact';

    public function setEmailRemainderOfExpiryPassword($token, $data)
    {
        return $this->sendRequest(
            $token,
            MotApi::METHOD_POST,
            self::PATH_PASSWORD_EXPIRY_NOTIFICATION,
            $data
        );
    }

    public function validateToken($token, $passwordResetToken, $param)
    {
        return $this->sendRequest(
            $token,
            MotApi::METHOD_GET,
            str_replace("{token}", $passwordResetToken, self::PATH_RESET.self::PATH_RESET_VALIDATE_TOKEN),
            $param
        );
    }

    public function generateToken($token, $param)
    {
        return $this->sendRequest(
            $token,
            MotApi::METHOD_POST,
            self::PATH_RESET,
            $param
        );
    }

    public function changePasswordWithToken($token, $param){
        return $this->sendRequest(
            $token,
            MotApi::METHOD_POST,
            self::PATH_RESET_CHANGE_PASSWORD_WITH_TOKEN,
            $param
        );
    }

    public function getPersonMotTestingClasses($token, $user_id)
    {
        return $this->sendRequest(
            $token,
            MotApi::METHOD_GET,
            str_replace('{user_id}', $user_id, self::PATH).'/mot-testing'
        );
    }

    /**
     * @param string $token
     * @param int $user_id
     * @return \Dvsa\Mot\Behat\Support\Response
     */
    public function getPersonDashboard($token, $user_id)
    {
        return $this->sendRequest(
            $token,
            MotApi::METHOD_GET,
            str_replace('{user_id}', $user_id, self::PATH).self::PATH_DASHBOARD
        );
    }

    /**
     * @param string $token
     * @param int $user_id
     * @return \Dvsa\Mot\Behat\Support\Response
     */
    public function getPersonStats($token, $user_id)
    {
        return $this->sendRequest(
            $token,
            MotApi::METHOD_GET,
            str_replace('{user_id}', $user_id, self::PATH).self::PATH_STATS
        );
    }

    /**
     * @param string $token
     * @param int $user_id
     * @return \Dvsa\Mot\Behat\Support\Response
     */
    public function getPersonRBAC($token, $user_id)
    {
        return $this->sendRequest(
            $token,
            MotApi::METHOD_GET,
            str_replace('{user_id}', $user_id, self::PATH).self::PATH_RBAC_ROLES
        );
    }

    public function getPersonDetails($token, $user_id)
    {
        return $this->sendRequest(
            $token,
            MotApi::METHOD_GET,
            str_replace('{user_id}', $user_id, self::PATH_PERSONAL_DETAILS)
        );
    }

    /**
     * @param string $token
     * @param int $user_id
     * @param string $roleCode
     * @return \Dvsa\Mot\Behat\Support\Response
     */
    public function addPersonRole($token, $user_id, $roleCode)
    {
        $jsonData = [
            'personSystemRoleCode' => $roleCode
        ];
        return $this->sendRequest(
            $token,
            MotApi::METHOD_POST,
            str_replace('{user_id}', $user_id, self::PATH.self::PATH_ROLES),
            $jsonData
        );
    }

    /**
     * @param $token
     * @param $user_id
     * @param $roleCode
     * @return \Dvsa\Mot\Behat\Support\Response
     */
    public function removePersonRole($token, $user_id, $roleCode)
    {
        $uri = str_replace(['{user_id}', '{role}'], [$user_id, $roleCode], self::PATH . self::PATH_ROLES_ROLE);
        return $this->sendRequest(
            $token,
            MotApi::METHOD_DELETE,
            $uri,
            []
        );
    }

    public function updateUserEmail($token, $user_id, $newEmail)
    {
        $body = [
            'email' => $newEmail,
        ];

        return $this->sendRequest(
            $token,
            MotApi::METHOD_PATCH,
            str_replace('{user_id}', $user_id, self::PATH . self::PATH_EMAIL),
            $body
        );
    }

    public function addLicence($token, $userId, $licenceData)
    {
        return $this->sendRequest(
            $token,
            MotApi::METHOD_GET,
            str_replace('{user_id}', $userId, self::PATH.self::PATH_LICENCE_UPDATE),
            $licenceData
        );
    }

    public function changePassword($token, $user_id, array $data)
    {
        return $this->sendRequest(
            $token,
            MotApi::METHOD_PUT,
            str_replace('{user_id}', $user_id, self::PATH.self::PATH_PASSWORD),
            $data
        );
    }

    public function changeName($token, $user_id, array $data)
    {
        return $this->sendRequest(
            $token,
            MotApi::METHOD_POST,
            str_replace('{user_id}', $user_id, self::PATH.self::PATH_NAME),
            $data
        );
    }

    public function changeAddress($token, $user_id, array $data)
    {
        return  $this->sendRequest(
            $token,
            MotApi::METHOD_POST,
            str_replace('{user_id}', $user_id, self::PATH.self::PATH_ADDRESS),
            $data
        );
    }

    public function changeDateOfBirth($token, $userId, array $data)
    {
        return $this->sendRequest(
            $token,
            MotApi::METHOD_POST,
            str_replace('{user_id}', $userId, self::PATH.self::PATH_DATE_OF_BIRTH),
            $data
        );
    }

    /**
     * @param string      $token
     * @param int|string  $userId
     * @param array       $telephoneData
     * @return \Dvsa\Mot\Behat\Support\Response
     */
    public function changeTelephoneNumber($token, $userId, array $telephoneData)
    {
        return $this->sendRequest(
            $token,
            MotApi::METHOD_PUT,
            str_replace('{user_id}', $userId, self::PATH.self::PATH_TELEPHONE_NUMBER),
            $telephoneData
        );
    }
}
