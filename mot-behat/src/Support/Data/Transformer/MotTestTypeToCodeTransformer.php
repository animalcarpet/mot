<?php
namespace Dvsa\Mot\Behat\Support\Data\Transformer;

use Dvsa\Mot\Behat\Support\Scope\BeforeBehatScenarioScope;
use DvsaCommon\Enum\MotTestTypeCode;

trait MotTestTypeToCodeTransformer
{
    /**
     * @Transform :testType
     */
    public function castMotTestTypeToCode($testType)
    {
        if (BeforeBehatScenarioScope::isTransformerDisabled()) {
            return $testType;
        }

        switch ($testType) {
            case "normal":
                return MotTestTypeCode::NORMAL_TEST;
            case "demo":
                return MotTestTypeCode::DEMONSTRATION_TEST_FOLLOWING_TRAINING;
            case "Targeted Reinspection":
                return MotTestTypeCode::TARGETED_REINSPECTION;
            case "MOT Compliance Survey":
                return MotTestTypeCode::MOT_COMPLIANCE_SURVEY;
            case "Inverted Appeal":
                return MotTestTypeCode::INVERTED_APPEAL;
            case "Statutory Appeal":
                return MotTestTypeCode::STATUTORY_APPEAL;
            default:
                throw new \InvalidArgumentException(sprintf("Cannot cast mot test type '%s' to code.", $testType));
        }
    }
}