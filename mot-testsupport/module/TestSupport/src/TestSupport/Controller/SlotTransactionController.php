<?php

namespace TestSupport\Controller;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManager;
use DvsaCommon\Utility\ArrayUtils;
use TestSupport\Helper\TestDataResponseHelper;
use TestSupport\Service\SlotTransactionService;

/**
 * Class SlotTransactionController.
 *
 * Creates transactions in the test_slot_transaction table
 */
class SlotTransactionController extends BaseTestSupportRestfulController
{
    /**
     * @param mixed $data
     *
     * @return \Zend\View\Model\JsonModel
     */
    public function create($data)
    {
        /** @var EntityManager $entityManager */
        /** @var Connection $connection */
        /** @var SlotTransactionService $slotService */
        $organisation = ArrayUtils::get($data, 'receiptReference');
        $amount = ArrayUtils::get($data, 'amount');
        $paymentType = (int) ArrayUtils::get($data, 'paymentType');
        $slots = ArrayUtils::get($data, 'slots');
        $slotService = $this->getServiceLocator()->get(SlotTransactionService::class);
        $result = $slotService->createSlotTransaction($organisation, $slots, $amount, $paymentType);

        return TestDataResponseHelper::jsonOk($result);
    }
}
