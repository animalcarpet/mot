<?php

namespace TestSupport\Service;

use Doctrine\ORM\EntityManager;
use TestSupport\Helper\TestDataResponseHelper;
use Zend\View\Model\JsonModel;

class InactiveTesterService
{
    const SUSP_STATUS_ID = 11;

    /**
     * @var TesterService
     */
    protected $testerService;

    /**
     * @var EntityManager
     */
    protected $entityManager;

    public function __construct(TesterService $testerService, EntityManager $entityManager)
    {
        $this->testerService = $testerService;
        $this->entityManager = $entityManager;
    }

    /**
     * Create a tester with the data supplied.
     *
     * @param array $data
     *
     * @return JsonModel
     */
    public function create($data)
    {
        if (empty($data['personId'])) {
            /** @var JsonModel $resp */
            $resp = $this->testerService->create($data);
            $personId = $resp->getVariable('data')['personId'];
        } else {
            $personId = $data['personId'];
            $resp = TestDataResponseHelper::jsonOk(
                [
                    'message' => 'Tester suspended',
                ]
            );
        }
        $this->suspendTestingAuthorisation($personId);

        return $resp;
    }

    /**
     * @param $personId
     */
    private function suspendTestingAuthorisation($personId)
    {
        $stmt = $this->entityManager->getConnection()->prepare(
            'UPDATE auth_for_testing_mot SET status_id = ? WHERE person_id = ?'
        );

        $stmt->bindValue(1, self::SUSP_STATUS_ID);
        $stmt->bindValue(2, $personId);
        $stmt->execute();
    }
}
