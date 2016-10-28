<?php
namespace Dvsa\Mot\Behat\Support\Data\Transformer;

use Dvsa\Mot\Behat\Support\Scope\BeforeBehatScenarioScope;
use DvsaCommon\Enum\MotTestStatusCode;

trait MotTestStatusToCodeTransformer
{
    /**
     * @Transform :testStatus
     */
    public function castMotTestStatusToCode($status)
    {
        if (BeforeBehatScenarioScope::isTransformerDisabled()) {
            return $status;
        }

        switch ($status) {
            case "passed":
                return MotTestStatusCode::PASSED;
            case "failed":
                return MotTestStatusCode::FAILED;
            default:
                throw new \InvalidArgumentException(sprintf("Cannot cast mot test status '%s' to code.", $status));
        }
    }
}