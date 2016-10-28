<?php

namespace Dvsa\Mot\Behat\Support\Api;

use Dvsa\Mot\Behat\Datasource\Authentication;
use Dvsa\Mot\Behat\Support\Request;

class SecurityPin extends MotApi
{
    const PATH = 'person/{user_id}/reset-pin';

    /**
     * @param $user_id
     *
     * @return Response
     */
    public function resetPin($user_id, $token)
    {
        $url = str_replace('{user_id}', $user_id, self::PATH);

        return $this->sendPutRequest(
            $token,
            $url
        );
    }
}
