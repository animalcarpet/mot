<?php

namespace Dvsa\Mot\Behat\Support\Api;

class MotTestingCertificate extends MotApi
{
    const PATH = "person/{person_id}/mot-testing-certificate";

    public function createCertificate($token, $personId, array $data)
    {
        $path = str_replace("{person_id}", $personId, self::PATH);

        return $this->sendRequest(
            $token,
            MotApi::METHOD_POST,
            $path,
            $data
        );
    }

    public function removeCertificate($token, $personId, $group)
    {
        return $this->sendRequest(
            $token,
            MotApi::METHOD_DELETE,
            $this->getPathForGroup($personId, $group)
        );
    }

    private function getPathForGroup($personId, $group)
    {
        $path = str_replace("{person_id}", $personId, self::PATH);
        return $path . "/" . strtolower($group);
    }
}
