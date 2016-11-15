<?php
namespace Dvsa\Mot\Behat\Support\Data\Transformer;

use DvsaCommon\Enum\MotTestStatusCode;

trait MotTestStatusToCodeTransformer
{
    /**
     * @Transform :testStatus
     */
    public function castMotTestStatusToCode($status)
    {
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