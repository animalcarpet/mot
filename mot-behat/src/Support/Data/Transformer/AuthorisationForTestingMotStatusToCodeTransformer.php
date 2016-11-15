<?php
namespace Dvsa\Mot\Behat\Support\Data\Transformer;

use DvsaCommon\Enum\AuthorisationForTestingMotStatusCode;

trait AuthorisationForTestingMotStatusToCodeTransformer
{
    /**
     * @Transform :authForTestingMotStatus
     */
    public function castAuthorisationForTestingMotStatusToCode($status)
    {
        switch ($status) {
            case "Unknown":
                $code = AuthorisationForTestingMotStatusCode::UNKNOWN;
                break;
            case "Initial Training Needed":
                $code = AuthorisationForTestingMotStatusCode::INITIAL_TRAINING_NEEDED;
                break;
            case "Demo Test Needed":
                $code = AuthorisationForTestingMotStatusCode::DEMO_TEST_NEEDED;
                break;
            case "Qualified":
                $code = AuthorisationForTestingMotStatusCode::QUALIFIED;
                break;
            case "Refresher Needed":
                $code = AuthorisationForTestingMotStatusCode::REFRESHER_NEEDED;
                break;
            case "Suspended":
                $code = AuthorisationForTestingMotStatusCode::SUSPENDED;
                break;
            default:
                throw new \InvalidArgumentException('Status \"' . $status . '\" not found');
        }

        return $code;
    }
}