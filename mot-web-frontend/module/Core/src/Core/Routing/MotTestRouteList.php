<?php

namespace Core\Routing;

class MotTestRouteList
{
    const MOT_CHECKLIST_PDF = 'mot-test/options/mot-checklist';
    const MOT_TEST_PRINT_DUPLICATE_TEST_RESULT = 'print-duplicate-test-result';
    const MOT_TEST_LOGS = 'tester-mot-test-log';
    const MOT_TEST_CERTIFICATE_SEARCH_BY_REGISTRATION = 'replacement-certificate-vehicle-search';
    const MOT_TEST_CERTIFICATE_SEARCH_BY_VIN = 'replacement-certificate-vehicle-search-vin';
    const MOT_TEST_CERTIFICATE_SEARCH_RESULTS = 'vehicle-certificates';
    const MOT_TEST_CERTIFICATE_VIEW = 'mot-test-certificate';
    const MOT_TEST_CERTIFICATE_PRINT = 'mot-test/print-duplicate-certificate';
    const MOT_TEST_CERTIFICATE_EDIT = 'mot-test/replacement-certificate';
    const MOT_TEST_START_TEST = 'start-test-confirmation';
    const MOT_TEST_START_TRAINING_TEST = 'start-training-test-confirmation';
    const MOT_TEST_START_NON_MOT_TEST = 'start-non-mot-test-confirmation';
    const MOT_TEST = 'mot-test';
}
