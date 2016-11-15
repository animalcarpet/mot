<?php
namespace Dvsa\Mot\Behat\Support\Data\Transformer;

use DvsaCommon\Enum\ReasonForCancelId;

trait ReasonForCancelToIdTransformer
{
    /**
     * @Transform :reasonForCancelId
     */
    public function castReasonForCancelToId($reason)
    {
        switch ($reason) {
            case "VTS incident":
                return ReasonForCancelId::VTSIN;
            case "Incorrect location":
                return ReasonForCancelId::LOCTN;
            case "Test equipment issue":
                return ReasonForCancelId::EQUIP;
            case "Accident or illness of tester":
                return ReasonForCancelId::ILLNS;
            case "The registration document issue":
                return ReasonForCancelId::NODOC;
            case "Dirty vehicle":
                return ReasonForCancelId::DIRTY;
            case "Lack of fuel or oil":
                return ReasonForCancelId::FUEL;
            case "Insecurity of a load":
                return ReasonForCancelId::LOAD;
            case "The vehicle size issue":
                return ReasonForCancelId::SIZE;
            case "Smoke issue":
                return ReasonForCancelId::SMOKE;
            case "Inability to open any device":
                return ReasonForCancelId::OPEN;
            case "Inspection may be dangerous or cause damage":
                return ReasonForCancelId::DANGR;
            case "Diesel engine vehicle is suspect":
                return ReasonForCancelId::DIESL;
            case "For classes I and II the frame is stamped":
                return ReasonForCancelId::STAMP;
            case "System unavailability":
                return ReasonForCancelId::SYSTM;
            case "Aborted by VE":
                return ReasonForCancelId::ABORT;
            case "Aborted due to change of ownership via seamless transfer":
                return ReasonForCancelId::OWNER;
            case "Vehicle registered in error":
                return ReasonForCancelId::ERROR;
            case "Test registered in error":
                return ReasonForCancelId::ERORX;
            default:
                throw new \InvalidArgumentException(sprintf("Cannot cast reason for cancel '%s' to id.", $reason));
        }
    }
}
