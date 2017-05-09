<?php

namespace DvsaMotApiTest\Controller;

use DvsaCommon\Enum\MotTestTypeCode;
use DvsaEntities\Entity\MotTest;
use DvsaMotApi\Controller\MotTestController;

/**
 * Class MotTestControllerMockSupport.
 */
class MotTestControllerMockSupport extends MotTestController
{
    /**
     * Avoids three levels of mock setup just to get a string.
     *
     * @param MotTest $motTest
     *
     * @return string fixed for test
     */
    protected function getMotTestType($motTest)
    {
        return MotTestTypeCode::TARGETED_REINSPECTION;
    }

    public function getId()
    {
        return 31415;
    }

    public function getUsername()
    {
        return 'validUser';
    }
}
