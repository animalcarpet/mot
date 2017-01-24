<?php

namespace Dvsa\Mot\Behat\Support\Api;

use Dvsa\Mot\Behat\Support\Request;

class SpecialNotice extends MotApi
{
    const PATH_SN_CONTENT = 'special-notice-content';
    const PATH_SN_BROADCAST = 'special-notice-broadcast';
    const PATH_SN_PUBLISH = 'special-notice-content/{sn_id}/publish';
    const PATH_SN_PERSON = 'person/{person_id}/special-notice';

    public function sendBroadcast($token)
    {
        $result = $this->sendPostRequest(
            $token,
            self::PATH_SN_BROADCAST
        );

        $data = $result->getBody()->getData();
        return (isset($data['success'])) ? $data['success'] : false;
    }

    /**
     * @param $token
     * @param array $data
     *
     * @return Response
     */
    public function createSpecialNotice($token, array $data = [])
    {
        $default = [
            'noticeTitle' => 'Special Notice title',
            'internalPublishDate' => (new \DateTime())->format("Y-m-d"),
            'externalPublishDate' => (new \DateTime("tomorrow"))->format("Y-m-d"),
            'acknowledgementPeriod' => '1',
            'noticeText' => 'upper',
            'targetRoles' => ['DVSA', "VTS"],
        ];

        $default = array_replace($default, $data);

        return $this->sendPostRequest(
            $token,
            self::PATH_SN_CONTENT,
            $default
        );
    }

    public function publish($token, $id)
    {
        return $this->sendPutRequest(
            $token,
            str_replace("{sn_id}", $id, self::PATH_SN_PUBLISH)

        );
    }

    public function getSpecialNotices($token, $personId)
    {
        return $this->sendGetRequest(
            $token,
            str_replace("{person_id}", $personId, self::PATH_SN_PERSON)
        );
    }

    public function getAllSpecialNotices($token)
    {
        return $this->sendGetRequest(
            $token,
            self::PATH_SN_CONTENT . "?" . http_build_query(["listAll" => true])
        );
    }

    public function removeSpecialNotices($token, $id)
    {
        return $this->sendDeleteRequest(
            $token,
            self::PATH_SN_CONTENT . "/" . $id
        );
    }
}
