<?php

namespace Core\Routing;

class VehicleRouteList
{
    const VEHICLE_DETAIL = 'vehicle/detail';
    const VEHICLE_MOT_HISTORY = 'vehicle/detail/history';
    const VEHICLE_SEARCH = 'vehicle/search';
    const VEHICLE_SEARCH_RESULTS = 'vehicle/result';
    const VEHICLE_CHANGE_ENGINE = 'vehicle/detail/change/engine';
    const VEHICLE_CHANGE_CLASS = 'vehicle/detail/change/class';
    const VEHICLE_CHANGE_UNDER_TEST_ENGINE = 'vehicle/detail/change-under-test/engine';
    const VEHICLE_CHANGE_UNDER_TEST_CLASS = 'vehicle/detail/change-under-test/class';
    const VEHICLE_CHANGE_UNDER_TEST_COLOUR = 'vehicle/detail/change-under-test/colour';
    const VEHICLE_CHANGE_FIRST_USED_DATE = 'vehicle/detail/change/first-used-date';
    const VEHICLE_CHANGE_MAKE_AND_MODEL = 'vehicle/detail/change/make-and-model';
    const VEHICLE_CHANGE_COLOUR = 'vehicle/detail/change/colour';
    const VEHICLE_ENFORCEMENT_MASK = 'vehicle/detail/mask';
    const VEHICLE_ENFORCEMENT_MASKED_SUCCESSFULLY = 'vehicle/detail/masked-successfully';
    const VEHICLE_ENFORCEMENT_UNMASK = 'vehicle/detail/unmask';
    const VEHICLE_ENFORCEMENT_UNMASKED_SUCCESSFULLY = 'vehicle/detail/unmasked-successfully';
}
