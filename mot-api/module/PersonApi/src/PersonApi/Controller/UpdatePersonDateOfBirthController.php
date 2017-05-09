<?php

namespace PersonApi\Controller;

use DvsaCommonApi\Controller\AbstractDvsaRestfulController;
use DvsaCommonApi\Model\ApiResponse;
use PersonApi\Service\PersonDateOfBirthService;

class UpdatePersonDateOfBirthController extends AbstractDvsaRestfulController
{
    /**
     * @var PersonDateOfBirthService
     */
    private $personDayOfBirthService;

    public function __construct(PersonDateOfBirthService $personDayOfBirthService)
    {
        $this->personDayOfBirthService = $personDayOfBirthService;
    }

    public function create($data)
    {
        $personId = $this->params()->fromRoute('id');

        $this->personDayOfBirthService->update($personId, $data);

        return ApiResponse::jsonOk();
    }
}
