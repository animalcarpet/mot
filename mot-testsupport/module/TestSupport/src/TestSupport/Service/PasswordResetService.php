<?php

namespace TestSupport\Service;

use TestSupport\Helper\TestDataResponseHelper;
use DvsaCommon\UrlBuilder\AccountUrlBuilder;
use DvsaCommon\Utility\ArrayUtils;
use TestSupport\Helper\TestSupportRestClientHelper;
use TestSupport\Helper\TestSupportAccessTokenManager;

class PasswordResetService
{
    /**
     * @var TestSupportRestClientHelper
     */
    protected $restClientHelper;

    public function __construct(TestSupportRestClientHelper $restClientHelper)
    {
        $this->restClientHelper = $restClientHelper;
    }

    /**
     * @param array $data
     *
     * @return JsonModel
     */
    public function create(array $data)
    {
        TestSupportAccessTokenManager::addSchemeManagerAsRequestorIfNecessary($data);

        $result = $this->restClientHelper->getJsonClient($data)->post(
            AccountUrlBuilder::resetPassword()->toString(),
            ['userId' => ArrayUtils::tryGet($data, 'userId', 1)]
        );

        return TestDataResponseHelper::jsonOk($result);
    }
}
