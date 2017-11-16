<?php

namespace DvsaCommon\Constants;

/**
 * List of the name of the feature toggle to be user across the app
 */
class FeatureToggle
{
    const VTS_RISK_SCORE = 'vts.risk.score';
    const INFINITY_CONTINGENCY = 'infinity.contingency';
    const TWO_FA = '2fa.enabled';
    const TWO_FA_HARD_STOP = '2fa.hardstop.enabled';
    const NEW_HOMEPAGE = 'new_homepage';
    const VEHICLE_WEIGHT_FROM_VEHICLE = 'vehicle_weight_from_vehicle';
    const EU_ROADWORTHINESS = 'eu_roadworthiness';
    const RFR_CACHE = 'rfr_cache';
    const RFR_ELASTICSEARCH = 'rfr_elasticsearch';
    const GQR_REPORTS_3_MONTHS_OPTION = 'gqr_reports_3_months_option';
}
