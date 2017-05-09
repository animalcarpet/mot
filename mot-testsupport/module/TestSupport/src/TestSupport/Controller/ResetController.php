<?php

namespace TestSupport\Controller;

use TestSupport\Helper\TestDataResponseHelper;
use Zend\Mvc\Controller\AbstractRestfulController;

/**
 * Resets the DB.
 */
class ResetController extends AbstractRestfulController
{
    public function deleteList()
    {
        $output = [];
        $returnVar = null;
        /*
         * DB username and password are deliberately hardcoded here.
         * This module should not be deployed to production, but
         * even if it was (by mistake), the password would be wrong.
         */
        exec(
            'cd /opt/dvsa/mot-api/db/ && /opt/dvsa/mot-api/db/reset_db_with_test_data.sh root password localhost mot motdbuser N N',
            $output,
            $returnVar
        );
        if ($returnVar == 0) {
            return TestDataResponseHelper::jsonOk(['message' => 'DB has been reset', 'output' => $output]);
        } else {
            $this->getResponse()->setStatusCode(500);
            $outputMessage = 'DB Reset failed! Output: ['.implode(' ', $output).']';

            return TestDataResponseHelper::jsonError($outputMessage);
        }
    }
}
