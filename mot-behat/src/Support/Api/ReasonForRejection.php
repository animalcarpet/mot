<?php

namespace Dvsa\Mot\Behat\Support\Api;

use DVSA\MOT\Behat\Support\Data\Model\ReasonForRejection\ReasonForRejection as ReasonForRejectionModel;
use DvsaCommon\Enum\ReasonForRejectionTypeName;

class ReasonForRejection extends MotApi
{
    const REASONS_PATH = 'mot-test/{mot_test_id}/reasons-for-rejection';
    const REASON_PATH = 'mot-test/{mot_test_id}/reason-for-rejection';
    const TEST_ITEM_SELECTOR_PATH = 'mot-test/{mot_test_id}/test-item-selector/{tisId}';

    protected $defaultRfrDetails = [
        'locationLateral' => 'nearside',
        'locationLongitudinal' => 'front',
        'locationVertical' => 'upper',
        'comment' => 'original comment',
        'failureDangerous' => false,
    ];

    public function addAdvisory($accessToken, $motTestNumber, $rfrId)
    {
        return $this->addRfr($accessToken, $motTestNumber, $rfrId, ReasonForRejectionTypeName::ADVISORY);
    }

    public function addFailure($token, $mot_test_number, $rfrId = null)
    {
        if ($rfrId === null) {
            $rfrId = ReasonForRejectionModel::getGroupB()->getForClass4();
        }

        return $this->addRfr($token, $mot_test_number, $rfrId, ReasonForRejectionTypeName::FAIL);
    }

    public function addPrs($token, $mot_test_number, $rfrId = null)
    {
        if ($rfrId === null) {
            $rfrId = ReasonForRejectionModel::getGroupB()->getForClass4Prs();
        }

        return $this->addRfr($token, $mot_test_number, $rfrId, ReasonForRejectionTypeName::PRS);
    }

    /** Change to ReasonForRejectionTypeName::DANGEROUS when field is added to reason_for_rejection_type table */
    public function addDangerousDefect($token, $mot_test_number, $rfrId)
    {
        return $this->addRfr($token, $mot_test_number, $rfrId, ReasonForRejectionTypeName::FAIL);
    }

    /** Change to ReasonForRejectionTypeName::MAJOR when field is added to reason_for_rejection_type table */
    public function addMajorDefect($token, $mot_test_number, $rfrId)
    {
        return $this->addRfr($token, $mot_test_number, $rfrId, ReasonForRejectionTypeName::FAIL);
    }

    /** Change to ReasonForRejectionTypeName::MINOR when field is added to reason_for_rejection_type table */
    public function addMinorDefect($token, $mot_test_number, $rfrId)
    {
        return $this->addRfr($token, $mot_test_number, $rfrId, ReasonForRejectionTypeName::ADVISORY);
    }

    public function addRfr($accessToken, $motTestNumber, $rdrId, $rfrType)
    {
        $body = array_merge(
            [
                'rfrId' => $rdrId,
                'type' => $rfrType,
            ],
            $this->defaultRfrDetails
        );

        return $this->postRfrToApi($accessToken, $motTestNumber, $body);
    }


    public function editRFR($accessToken, $motTestNumber, $rfrId = null)
    {
        if ($rfrId === null) {
            $rfrId = ReasonForRejectionModel::getGroupB()->getForClass4();
        }

        $this->defaultRfrDetails['locationLateral'] = 'central';
        $this->defaultRfrDetails['locationLongitudinal'] = "rear";
        $this->defaultRfrDetails['locationVertical'] = "inner";
        $this->defaultRfrDetails['comment'] = "edited comment";

        $body = array_merge(
            [
                'id' => $rfrId,
            ],
            $this->defaultRfrDetails
        );

        return $this->postRfrToApi($accessToken, $motTestNumber, $body);
    }

    public function removeRFR($accessToken, $motTestNumber, $rfrId)
    {
        $body = array_merge(
            [
                'id' => $rfrId,
            ],
            $this->defaultRfrDetails
        );

        return $this->postRfrToApi($accessToken, $motTestNumber, $body);
    }

    /**
     * @param $token
     * @param $motTestNumber
     * @param $term
     * @return \Dvsa\Mot\Behat\Support\Response
     */
    public function search($token, $motTestNumber, $term, $start = null, $end = null)
    {
        $path = str_replace(['{mot_test_id}', '{term}'], [$motTestNumber, $term], self::REASON_PATH);
        $path .= "?search=" . $term;
        if (!is_null($start)) {
            $path .= "&start=" . $start;
        }
        if (!is_null($end)) {
            $path .= "&end=" . $end;
        }

        $response = $this->sendRequest(
            $token,
            MotApi::METHOD_GET,
            $path
        );

        \PHPUnit_Framework_Assert::assertEquals($response->getStatusCode(), 200);

        return $response;
    }

    /**
     * @param $token
     * @param $motTestNumber
     * @param $rootItemId
     * @return \Dvsa\Mot\Behat\Support\Response
     */
    public function listTestItemSelectors($token, $motTestNumber, $rootItemId = 0)
    {
        $path = str_replace(['{mot_test_id}', '{tisId}'], [$motTestNumber, $rootItemId], self::TEST_ITEM_SELECTOR_PATH);

        return $this->sendRequest(
            $token,
            MotApi::METHOD_GET,
            $path
        );
    }

    /**
     * @param string $accessToken
     * @param int $motTestNumber
     * @param array $body
     * @return \Dvsa\Mot\Behat\Support\Response
     */
    private function postRfrToApi($accessToken, $motTestNumber, $body)
    {
        $response = $this->sendRequest(
            $accessToken,
            self::METHOD_POST,
            str_replace('{mot_test_id}', $motTestNumber, self::REASONS_PATH),
            $body
        );

        \PHPUnit_Framework_Assert::assertEquals($response->getStatusCode(), 200);

        return $response;
    }
}
