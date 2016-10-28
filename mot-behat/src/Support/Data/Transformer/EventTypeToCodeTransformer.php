<?php
namespace Dvsa\Mot\Behat\Support\Data\Transformer;

use Dvsa\Mot\Behat\Support\Scope\BeforeBehatScenarioScope;
use DvsaCommon\Enum\EventTypeCode;

trait EventTypeToCodeTransformer
{
    /**
     * @Transform :eventType
     */
    public function castEventTypeToCode($eventType)
    {
        if (BeforeBehatScenarioScope::isTransformerDisabled()) {
            return $eventType;
        }

        switch ($eventType) {
            case "create security card order":
                return EventTypeCode::CREATE_SECURITY_CARD_ORDER;
            default:
                throw new \InvalidArgumentException(sprintf("Cannot cast event type '%s' to code.", $eventType));
        }
    }
}