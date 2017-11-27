<?php

namespace DvsaMotTest\Service;

use DvsaCommon\HttpRestJson\Client as HttpClient;
use DvsaCommon\UrlBuilder\MotTestUrlBuilder;
use DvsaCommon\UrlBuilder\UrlBuilder;
use DvsaCommon\Utility\ArrayUtils;

class ReplacementCertificateDraftService
{
    /** @var HttpClient */
    private $httpClient;

    /**
     * @param HttpClient $httpClient
     */
    public function __construct(HttpClient $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    /**
     * @param int $draftId
     * @param string $motTestNumber
     * @return array|null
     */
    public function getDraft(int $draftId, string $motTestNumber)
    {
        $apiPath = UrlBuilder::replacementCertificateDraft($draftId, $motTestNumber);

        return ArrayUtils::tryGet($this->httpClient->get((string) $apiPath), 'data');
    }

    /**
     * @param string $motTestNumber
     * @return int
     */
    public function createDraft(string $motTestNumber) : int
    {
        $draftId = $this->httpClient->post(
            UrlBuilder::replacementCertificateDraft(null, $motTestNumber),
            ['motTestNumber' => $motTestNumber]
        )['data']['id'];

        return $draftId;
    }

    /**
     * @param int $draftId
     * @param string $motTestNumber
     * @param array $data
     * @return mixed
     */
    public function updateDraft(int $draftId, string $motTestNumber, array $data)
    {
        $url = UrlBuilder::replacementCertificateDraft($draftId, $motTestNumber);

        return $this->httpClient->put($url, $data);
    }

    /**
     * @param int $draftId
     * @param string $motTestNumber
     * @return array
     */
    public function getDraftDiff(int $draftId, string $motTestNumber)
    {
        $url = UrlBuilder::replacementCertificateDraftDiff($draftId, $motTestNumber);

        return $this->httpClient->get($url)['data'];
    }

    /**
     * @param int $draftId
     * @param string $motTestNumber
     * @param string|null $oneTimePassword
     * @return mixed|string
     */
    public function applyDraft(int $draftId, string $motTestNumber, string $oneTimePassword = null)
    {
        $url = UrlBuilder::replacementCertificateDraftApply($draftId, $motTestNumber);
        $data = ['oneTimePassword' => $oneTimePassword];

        return $this->httpClient->post($url, $data);
    }

    /**
     * @return array
     */
    public function getChangeOfTesterReasons()
    {
        return $this->httpClient->get('cert-change-diff-tester-reason')['data'];
    }

    /**
     * @param int $draftId
     * @param string $motTestNumber
     * @param string $reasonCode
     * @return mixed
     */
    public function updateDraftReasonForDifferentTester(int $draftId, string $motTestNumber, string $reasonCode)
    {
        $data = ['reasonForDifferentTester' => $reasonCode];

        return $this->updateDraft($draftId, $motTestNumber, $data);
    }

    /**
     * @param string $motTestNumber
     * @return mixed
     */
    public function isOdometerReadingEditable(string $motTestNumber)
    {
        $url = MotTestUrlBuilder::odometerReadingModifyCheck($motTestNumber)->toString();

        $result = $this->httpClient->get($url);

        return $result['data']['modifiable'];
    }
}
