<?php

namespace Dvsa\Mot\Behat\Support\Data\Model;

class ReasonForRejectionEU
{
    const CATEGORY_NAME_ROAD_WHEELS = 'Identification of the Vehicle';

    /** DANGER */
    const RFR_VEHICLE_IDENTIFICATION_NUMBER_DANGEROUS = 21010;

    /** MAJOR */
    const RFR_REGISTRATION_PLATES_MAJOR = 21001;

    /** MINOR */
    const RFR_REGISTRATION_PLATES_MINOR = 21003;

    /** START DATED PAST */
    const RFR_START_DATED_PAST = 90000;

    /** END DATED PAST */
    const RFR_END_DATED_PAST = 90001;

    /** START DATED FUTURE */
    const RFR_START_DATED_FUTURE = 90002;
}
