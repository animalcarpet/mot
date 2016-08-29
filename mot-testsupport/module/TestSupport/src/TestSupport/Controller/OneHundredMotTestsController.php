<?php

namespace TestSupport\Controller;

use InvalidArgumentException;
use TestSupport\Helper\TestDataResponseHelper;
use TestSupport\Service\OneHundredMotTestsService;
use Zend\View\Model\JsonModel;

/**
 * Creates one hundred MOT tests for a user for use by satisfaction survey tests.
 *
 * Should not be deployed in production.
 */
class OneHundredMotTestsController extends TestSupportMotTestController
{
    /**
     * @param array $data
     *
     * @return JsonModel
     */
    public function create(array $data)
    {
        if (!isset($data['userId'])) {
            throw new InvalidArgumentException('Parameter "userId" is missing.');
        }

        /*
         * `intval` returns the integer value of var on success, or 0 on failure. Empty arrays return 0, non-empty
         *  arrays return 1.
         */
        $userId = intval($data['userId']);
        if ($userId <= 0) {
            throw new InvalidArgumentException(sprintf('Invalid value for "userId": %s. Should be "(int) 1" or upper',
                $data['userId']));
        }

        if (true === $this->getOneHundredMotTestsService()->create($userId)) {
            return TestDataResponseHelper::jsonOk(['success' => true]);
        }

        return TestDataResponseHelper::jsonError(['success' => false]);
    }

    /**
     * @return OneHundredMotTestsService
     */
    private function getOneHundredMotTestsService()
    {
        return $this->getServiceLocator()->get(OneHundredMotTestsService::class);
    }

}
