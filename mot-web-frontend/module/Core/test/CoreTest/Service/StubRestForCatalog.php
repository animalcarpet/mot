<?php

namespace CoreTest\Service;

use DvsaCommon\Enum\BrakeTestTypeCode;
use DvsaCommon\Enum\EquipmentModelStatusCode;
use DvsaCommon\Enum\OrganisationBusinessRoleCode;
use DvsaCommon\Enum\SiteBusinessRoleCode;

/**
 * Minimal rest client stub for use by catalog service.
 */
class StubRestForCatalog
{
    public function get()
    {
        /*
         * You can update this JSON with
         * curl  --header "Authorization: Bearer ve-token"  http://mot-api/catalog  | python -mjson.tool > catalog.txt
         * then pasting catalog.txt
         */
        $result = json_decode('
{
    "data": {
        "categories": [
            {
                "category": "Not applicable",
                "id": 1
            },
            {
                "category": "Immediate",
                "id": 2
            },
            {
                "category": "Delayed",
                "id": 3
            },
            {
                "category": "Inspection notice",
                "id": 4
            }
        ],
"colours": [
            {
                "code": "A",
                "id": 1,
                "name": "Orange"
            },
            {
                "code": "B",
                "id": 2,
                "name": "Black"
            },
            {
                "code": "C",
                "id": 3,
                "name": "Cream"
            },
            {
                "code": "D",
                "id": 4,
                "name": "Yellow"
            },
            {
                "code": "E",
                "id": 5,
                "name": "Green"
            },
            {
                "code": "G",
                "id": 6,
                "name": "Gold"
            },
            {
                "code": "I",
                "id": 7,
                "name": "Pink"
            },
            {
                "code": "L",
                "id": 8,
                "name": "Blue"
            },
            {
                "code": "M",
                "id": 9,
                "name": "Multi-colour"
            },
            {
                "code": "N",
                "id": 10,
                "name": "Brown"
            },
            {
                "code": "O",
                "id": 11,
                "name": "Maroon"
            },
            {
                "code": "P",
                "id": 12,
                "name": "Purple"
            },
            {
                "code": "Q",
                "id": 13,
                "name": "Beige"
            },
            {
                "code": "R",
                "id": 14,
                "name": "Red"
            },
            {
                "code": "S",
                "id": 15,
                "name": "Silver"
            },
            {
                "code": "T",
                "id": 16,
                "name": "Turquoise"
            },
            {
                "code": "V",
                "id": 17,
                "name": "Violet"
            },
            {
                "code": "W",
                "id": 18,
                "name": "White"
            },
            {
                "code": "X",
                "id": 19,
                "name": "Not Stated/No other colour"
            },
            {
                "code": "Y",
                "id": 20,
                "name": "Grey"
            },
            {
                "code": "Z",
                "id": 21,
                "name": "Bronze"
            }
        ],
        "countryOfRegistration": [
            {
                "id" : 1,
                "code" : "GB",
                "name" : "GB, UK, ENG, CYM, SCO (UK) - Great Britain"
            },
            {
                "id" : 2,
                "code" : "NI",
                "name" : "GB, NI (UK) - Northern Ireland"
            },
            {
                "id" : 3,
                "code" : "GBA",
                "name" : "GBA (GG) - Alderney"
            },
            {
                "id" : 4,
                "code" : "GBG",
                "name" : "GBG (GG) - Guernsey"
            },
            {
                "id" : 5,
                "code" : "GBJ",
                "name" : "GBJ (JE) - Jersey"
            },
            {
                "id" : 6,
                "code" : "GBM",
                "name" : "GBM (IM) - Isle of Man"
            },
            {
                "id" : 7,
                "code" : "AT",
                "name" : "A (AT) - Austria"
            },
            {
                "id" : 8,
                "code" : "BE",
                "name" : "B (BE) - Belgium"
            },
            {
                "id" : 9,
                "code" : "BG",
                "name" : "BG (BG) - Bulgaria"
            },
            {
                "id" : 10,
                "code" : "CY",
                "name" : "CY (CY) - Cyprus"
            },
            {
                "id" : 11,
                "code" : "CZ",
                "name" : "CZ (CZ) - Czech Republic"
            },
            {
                "id" : 12,
                "code" : "DK",
                "name" : "DK (DK) - Denmark"
            },
            {
                "id" : 13,
                "code" : "EE",
                "name" : "EST (EE) - Estonia"
            },
            {
                "id" : 14,
                "code" : "FI",
                "name" : "FIN (FI) - Finland"
            },
            {
                "id" : 15,
                "code" : "FR",
                "name" : "F (FR) - France"
            },
            {
                "id" : 16,
                "code" : "DE",
                "name" : "D (DE) - Germany"
            },
            {
                "id" : 17,
                "code" : "GI",
                "name" : "GBZ (GI) - Gibraltar"
            },
            {
                "id" : 18,
                "code" : "GR",
                "name" : "GR (GR) - Greece"
            },
            {
                "id" : 19,
                "code" : "HU",
                "name" : "H (HU) - Hungary"
            },
            {
                "id" : 20,
                "code" : "IE",
                "name" : "IRL (IE) - Ireland"
            },
            {
                "id" : 21,
                "code" : "IT",
                "name" : "I (IT) - Italy"
            },
            {
                "id" : 22,
                "code" : "LV",
                "name" : "LV (LV) - Latvia"
            },
            {
                "id" : 23,
                "code" : "LT",
                "name" : "LT (LT) - Lithuania"
            },
            {
                "id" : 24,
                "code" : "LU",
                "name" : "L (LU) - Luxembourg"
            },
            {
                "id" : 25,
                "code" : "MT",
                "name" : "M (MT) - Malta"
            },
            {
                "id" : 26,
                "code" : "NL",
                "name" : "NL (NL) - Netherlands"
            },
            {
                "id" : 27,
                "code" : "PL",
                "name" : "PL (PL) - Poland"
            },
            {
                "id" : 28,
                "code" : "PT",
                "name" : "P (PT) - Portugal"
            },
            {
                "id" : 29,
                "code" : "RO",
                "name" : "RO (RO) - Romania"
            },
            {
                "id" : 30,
                "code" : "SK",
                "name" : "SK (SK) - Slovakia"
            },
            {
                "id" : 31,
                "code" : "SI",
                "name" : "SLO (SI) - Slovenia"
            },
            {
                "id" : 32,
                "code" : "ES",
                "name" : "E (ES) - Spain"
            },
            {
                "id" : 33,
                "code" : "SE",
                "name" : "S (SE) - Sweden"
            },
            {
                "id" : 34,
                "code" : "XNEU",
                "name" : "Non EU"
            },
            {
                "id" : 35,
                "code" : "XUKN",
                "name" : "Not Known"
            },
            {
                "id" : 36,
                "code" : "XNA",
                "name" : "Not Applicable"
            }
        ],
        "decisions": [
            {
                "decision": "Not applicable",
                "id": 1
            },
            {
                "decision": "Defect missed",
                "id": 2
            },
            {
                "decision": "Incorrect decision",
                "id": 3
            }
        ],
        "demoTestResult": [
            "Satisfactory",
            "Unsatisfactory"
        ],
        "fuelTypes": [
            {
                "id": 1,
                "code": "PE",
                "name": "Petrol"
            },
            {
                "id": "2",
                "code": "DI",
                "name": "Diesel"
            },
            {
                "id": "3",
                "code": "EL",
                "name": "Electric"
            },
            {
                "id": "4",
                "code": "ST",
                "name": "Steam"
            },
            {
                "id": "6",
                "code": "CN",
                "name": "CNG"
            },
            {
                "id": "7",
                "code": "LN",
                "name": "LNG"
            },
            {
                "id": "5",
                "code": "LP",
                "name": "LPG"
            },
            {
                "id": "8",
                "code": "FC",
                "name": "Fuelcell"
            },
            {
                "id": "9",
                "code": "OT",
                "name": "Other"
            }
        ],
        "motTestType": [
            {
                "code": "NT",
                "createdBy": null,
                "createdOn": {
                    "date": "2014-11-07 16:03:01.310542",
                    "timezone": "UTC",
                    "timezone_type": 3
                },
                "description": "Normal Test",
                "id": 1,
                "isDemo": false,
                "isReinspection": false,
                "isSlotConsuming": true,
                "lastUpdatedBy": null,
                "lastUpdatedOn": null,
                "position": 1,
                "version": 1
            },
            {
                "code": "PL",
                "createdBy": null,
                "createdOn": {
                    "date": "2014-11-07 16:03:01.310542",
                    "timezone": "UTC",
                    "timezone_type": 3
                },
                "description": "Partial Retest Left VTS",
                "id": 2,
                "isDemo": false,
                "isReinspection": false,
                "isSlotConsuming": true,
                "lastUpdatedBy": null,
                "lastUpdatedOn": null,
                "position": 2,
                "version": 1
            },
            {
                "code": "PV",
                "createdBy": null,
                "createdOn": {
                    "date": "2014-11-07 16:03:01.310542",
                    "timezone": "UTC",
                    "timezone_type": 3
                },
                "description": "Partial Retest Repaired at VTS",
                "id": 3,
                "isDemo": false,
                "isReinspection": false,
                "isSlotConsuming": true,
                "lastUpdatedBy": null,
                "lastUpdatedOn": null,
                "position": 3,
                "version": 1
            },
            {
                "code": "ER",
                "createdBy": null,
                "createdOn": {
                    "date": "2014-11-07 16:03:01.310542",
                    "timezone": "UTC",
                    "timezone_type": 3
                },
                "description": "Targeted Reinspection",
                "id": 4,
                "isDemo": false,
                "isReinspection": true,
                "isSlotConsuming": false,
                "lastUpdatedBy": null,
                "lastUpdatedOn": null,
                "position": 4,
                "version": 1
            },
            {
                "code": "EC",
                "createdBy": null,
                "createdOn": {
                    "date": "2014-11-07 16:03:01.310542",
                    "timezone": "UTC",
                    "timezone_type": 3
                },
                "description": "MOT Compliance Survey",
                "id": 5,
                "isDemo": false,
                "isReinspection": true,
                "isSlotConsuming": false,
                "lastUpdatedBy": null,
                "lastUpdatedOn": null,
                "position": 5,
                "version": 1
            },
            {
                "code": "EI",
                "createdBy": null,
                "createdOn": {
                    "date": "2014-11-07 16:03:01.310542",
                    "timezone": "UTC",
                    "timezone_type": 3
                },
                "description": "Inverted Appeal",
                "id": 6,
                "isDemo": false,
                "isReinspection": true,
                "isSlotConsuming": false,
                "lastUpdatedBy": null,
                "lastUpdatedOn": null,
                "position": 6,
                "version": 1
            },
            {
                "code": "ES",
                "createdBy": null,
                "createdOn": {
                    "date": "2014-11-07 16:03:01.310542",
                    "timezone": "UTC",
                    "timezone_type": 3
                },
                "description": "Statutory Appeal",
                "id": 7,
                "isDemo": false,
                "isReinspection": true,
                "isSlotConsuming": false,
                "lastUpdatedBy": null,
                "lastUpdatedOn": null,
                "position": 7,
                "version": 1
            },
            {
                "code": "OT",
                "createdBy": null,
                "createdOn": {
                    "date": "2014-11-07 16:03:01.310542",
                    "timezone": "UTC",
                    "timezone_type": 3
                },
                "description": "Other",
                "id": 8,
                "isDemo": false,
                "isReinspection": true,
                "isSlotConsuming": false,
                "lastUpdatedBy": null,
                "lastUpdatedOn": null,
                "position": 8,
                "version": 1
            },
            {
                "code": "RT",
                "createdBy": null,
                "createdOn": {
                    "date": "2014-11-07 16:03:01.310542",
                    "timezone": "UTC",
                    "timezone_type": 3
                },
                "description": "Re-Test",
                "id": 9,
                "isDemo": false,
                "isReinspection": false,
                "isSlotConsuming": true,
                "lastUpdatedBy": null,
                "lastUpdatedOn": null,
                "position": 9,
                "version": 1
            },
            {
                "code": "DT",
                "createdBy": null,
                "createdOn": {
                    "date": "2014-11-07 16:03:01.310542",
                    "timezone": "UTC",
                    "timezone_type": 3
                },
                "description": "Demonstration Test following training",
                "id": 10,
                "isDemo": true,
                "isReinspection": false,
                "isSlotConsuming": false,
                "lastUpdatedBy": null,
                "lastUpdatedOn": null,
                "position": 10,
                "version": 1
            },
            {
                "code": "DR",
                "createdBy": null,
                "createdOn": {
                    "date": "2014-11-07 16:03:01.310542",
                    "timezone": "UTC",
                    "timezone_type": 3
                },
                "description": "Routine Demonstration Test",
                "id": 11,
                "isDemo": true,
                "isReinspection": false,
                "isSlotConsuming": false,
                "lastUpdatedBy": null,
                "lastUpdatedOn": null,
                "position": 11,
                "version": 1
            },
            {
                "code": "EN",
                "createdBy": null,
                "createdOn": {
                    "date": "2014-11-07 16:03:01.310542",
                    "timezone": "UTC",
                    "timezone_type": 3
                },
                "description": "Non-Mot Test",
                "id": 12,
                "isDemo": false,
                "isReinspection": false,
                "isSlotConsuming": false,
                "lastUpdatedBy": null,
                "lastUpdatedOn": null,
                "position": 12,
                "version": 1
            }
        ],
        "outcomes": [
            {
                "id": 1,
                "outcome": "No further action"
            },
            {
                "id": 2,
                "outcome": "Advisory warning letter"
            },
            {
                "id": 3,
                "outcome": "Disciplinary action report"
            }
        ],
        "reasonsForCancel": [
            {
                "_class": "DvsaCommon\\\\Dto\\\\Common\\\\ReasonForCancelDto",
                "abandoned": false,
                "id": 1,
                "reason": "Accident or illness of tester",
                "reasonCy": "Damwain neu salwch yr abrofwr",
                "reasonInLang": "Accident or illness of tester"
            },
            {
                "_class": "DvsaCommon\\\\Dto\\\\Common\\\\ReasonForCancelDto",
                "abandoned": false,
                "id": 2,
                "reason": "Aborted by VE",
                "reasonCy": "Aflwyddaf gan yr Archwiliwr Cerbydau",
                "reasonInLang": "Aborted by VE"
            },
            {
                "_class": "DvsaCommon\\\\Dto\\\\Common\\\\ReasonForCancelDto",
                "abandoned": false,
                "id": 3,
                "reason": "Vehicle registered in error",
                "reasonCy": "Cerbyd wedi ei gofrestru fel cangymeriad",
                "reasonInLang": "Vehicle registered in error"
            },
            {
                "_class": "DvsaCommon\\\\Dto\\\\Common\\\\ReasonForCancelDto",
                "abandoned": false,
                "id": 4,
                "reason": "Test equipment issue",
                "reasonCy": "Daill cyfarpar arbrofi",
                "reasonInLang": "Test equipment issue"
            },
            {
                "_class": "DvsaCommon\\\\Dto\\\\Common\\\\ReasonForCancelDto",
                "abandoned": false,
                "id": 5,
                "reason": "VTS incident",
                "reasonCy": "Digwyddiad Gorsaf Brofi Cerbydau",
                "reasonInLang": "VTS incident"
            },
            {
                "_class": "DvsaCommon\\\\Dto\\\\Common\\\\ReasonForCancelDto",
                "abandoned": false,
                "id": 6,
                "reason": "Incorrect location",
                "reasonCy": "Lleoliad anghywir",
                "reasonInLang": "Incorrect location"
            },
            {
                "_class": "DvsaCommon\\\\Dto\\\\Common\\\\ReasonForCancelDto",
                "abandoned": true,
                "id": 7,
                "reason": "Inspection may be dangerous or cause damage",
                "reasonCy": "Archwiliad yn beryglus neu yn achosi niwed",
                "reasonInLang": "Inspection may be dangerous or cause damage"
            }
        ],
        "reasonsForRefusal": [
            {
                "_class": "DvsaCommon\\\\Dto\\\\Common\\\\ReasonForRefusalDto",
                "id": 1,
                "reason": "Unable to identify date of first use",
                "reasonCy": "Methu unieithu dyddiad defnyddwyd gyntaf",
                "reasonInLang": "Unable to identify date of first use"
            },
            {
                "_class": "DvsaCommon\\\\Dto\\\\Common\\\\ReasonForRefusalDto",
                "id": 2,
                "reason": "Vehicle is too dirty to examine",
                "reasonCy": "Cerbyd rhy fydr i\u2019w archwilio",
                "reasonInLang": "Vehicle is too dirty to examine"
            },
            {
                "_class": "DvsaCommon\\\\Dto\\\\Common\\\\ReasonForRefusalDto",
                "id": 3,
                "reason": "The vehicle is not fit to be driven",
                "reasonCy": "Cerbyd ddim yn ffit i\u2019w ddreifio",
                "reasonInLang": "The vehicle is not fit to be driven"
            },
            {
                "_class": "DvsaCommon\\\\Dto\\\\Common\\\\ReasonForRefusalDto",
                "id": 4,
                "reason": "Insecurity of load or other items",
                "reasonCy": "Llwyth neu eitemau eraill yn anniogel",
                "reasonInLang": "Insecurity of load or other items"
            },
            {
                "_class": "DvsaCommon\\\\Dto\\\\Common\\\\ReasonForRefusalDto",
                "id": 5,
                "reason": "Vehicle configuration/size unsuitable",
                "reasonCy": "Cerbyd cyfluniad/maint anaddas",
                "reasonInLang": "Vehicle configuration/size unsuitable"
            },
            {
                "_class": "DvsaCommon\\\\Dto\\\\Common\\\\ReasonForRefusalDto",
                "id": 6,
                "reason": "Vehicle emits substantial smoke",
                "reasonCy": "Cerbyd yn alltafu m\u0175g sylweddol",
                "reasonInLang": "Vehicle emits substantial smoke"
            },
            {
                "_class": "DvsaCommon\\\\Dto\\\\Common\\\\ReasonForRefusalDto",
                "id": 7,
                "reason": "Unable to open device (door, boot, etc.)",
                "reasonCy": "Methu agor dyfais (dr\u0175s,lledrgist ayyb.)",
                "reasonInLang": "Unable to open device (door, boot, etc.)"
            },
            {
                "_class": "DvsaCommon\\\\Dto\\\\Common\\\\ReasonForRefusalDto",
                "id": 8,
                "reason": "Inspection may be dangerous or cause damage",
                "reasonCy": "Archiwliad yn beryglus neu yn achosi niwed",
                "reasonInLang": "Inspection may be dangerous or cause damage"
            },
            {
                "_class": "DvsaCommon\\\\Dto\\\\Common\\\\ReasonForRefusalDto",
                "id": 9,
                "reason": "Requested test fee not paid in advance",
                "reasonCy": "Ffi arborfi gofynedig heb ei dalu yn flaenorol",
                "reasonInLang": "Requested test fee not paid in advance"
            },
            {
                "_class": "DvsaCommon\\\\Dto\\\\Common\\\\ReasonForRefusalDto",
                "id": 10,
                "reason": "Suspect maintenance history of diesel engine",
                "reasonCy": "Hanes cynhaliaeth amheuol o beiriant diesel",
                "reasonInLang": "Suspect maintenance history of diesel engine"
            },
            {
                "_class": "DvsaCommon\\\\Dto\\\\Common\\\\ReasonForRefusalDto",
                "id": 11,
                "reason": "Motorcycle frame stamped not for road use",
                "reasonCy": "Ffram motorbeic wedi ei stampio ddim i`w ddefnyddio ar y ffordd",
                "reasonInLang": "Motorcycle frame stamped not for road use"
            },
            {
                "_class": "DvsaCommon\\\\Dto\\\\Common\\\\ReasonForRefusalDto",
                "id": 12,
                "reason": "VTS not authorised to test vehicle class",
                "reasonCy": "VTS heb ei awdurdodi i arbrofi dosbarth y cerbyd",
                "reasonInLang": "VTS not authorised to test vehicle class"
            }
        ],
        "reasonsForSiteVisit": [
            {
                "createdBy": null,
                "createdOn": {
                    "date": "2014-11-07 16:03:01.415406",
                    "timezone": "UTC",
                    "timezone_type": 3
                },
                "id": 1,
                "lastUpdatedBy": null,
                "lastUpdatedOn": null,
                "reason": "Directed site visit",
                "version": 1
            },
            {
                "createdBy": null,
                "createdOn": {
                    "date": "2014-11-07 16:03:01.415406",
                    "timezone": "UTC",
                    "timezone_type": 3
                },
                "id": 2,
                "lastUpdatedBy": null,
                "lastUpdatedOn": null,
                "reason": "Site approval visit",
                "version": 1
            }
        ],
        "reinspections": [
            {
                "decision": "Agreed fully with test result",
                "id": 1
            },
            {
                "decision": "Result correct but advisory warranted",
                "id": 2
            },
            {
                "decision": "Result incorrect",
                "id": 3
            },
            {
                "decision": "Other - enter details in section C",
                "id": 4
            }
        ],
        "scores": [
            {
                "description": "Disregard",
                "id": 1,
                "score": null
            },
            {
                "description": "Overruled, marginally wrong",
                "id": 2,
                "score": 0
            },
            {
                "description": "Obviously wrong",
                "id": 3,
                "score": 5
            },
            {
                "description": "Significantly wrong",
                "id": 4,
                "score": 10
            },
            {
                "description": "No defect",
                "id": 5,
                "score": 20
            },
            {
                "description": "Other defect missed",
                "id": 6,
                "score": 20
            },
            {
                "description": "Not testable",
                "id": 7,
                "score": 20
            },
            {
                "description": "Exs. corr/wear/damage missed",
                "id": 8,
                "score": 30
            },
            {
                "description": "Risk of injury missed",
                "id": 9,
                "score": 40
            }
        ],
        "testerStatus": [
            {
                "description": "Qualified",
                "id": 1
            },
            {
                "description": "Demonstration test needed",
                "id": 2
            },
            {
                "description": "Refresher needed",
                "id": 3
            },
            {
                "description": "Initial training needed",
                "id": 4
            }
        ],
        "transmissionType": [
            {
                "id": 1,
                "name": "Automatic"
            },
            {
                "id": 2,
                "name": "Manual"
            }
        ],
        "vehicleClass": [
            {
                "id": 1,
                "name": "1"
            },
            {
                "id": 2,
                "name": "2"
            },
            {
                "id": 3,
                "name": "3"
            },
            {
                "id": 4,
                "name": "4"
            },
            {
                "id": 5,
                "name": "5"
            },
            {
                "id": 7,
                "name": "7"
            }
        ],
        "visitOutcomes": [
            {
                "description": "Satisfactory",
                "id": 1
            },
            {
                "description": "Shortcomings found",
                "id": 2
            },
            {
                "description": "Abandoned",
                "id": 3
            }
        ],
        "organisationBusinessRole": [
            {
                "id": 1,
                "code": "' .OrganisationBusinessRoleCode::AUTHORISED_EXAMINER_DESIGNATED_MANAGER.'",
                "name": "Authorised Examiner Designated Manager"
            },
            {
                "id": 2,
                "code": "' .OrganisationBusinessRoleCode::AUTHORISED_EXAMINER_DELEGATE.'",
                "name": "Authorised Examiner Delegate"
            },
            {
                "id": 3,
                "code": "' .OrganisationBusinessRoleCode::AUTHORISED_EXAMINER_PRINCIPAL.'",
                "name": "Authorised Examiner Principal"
            },
            {
                "id": 4,
                "code": "' .OrganisationBusinessRoleCode::SCHEME_MANAGER.'",
                "name": "DVSA Scheme Management"
            }
        ],
        "siteBusinessRole": [
            {
                "code": "' .SiteBusinessRoleCode::TESTER.'",
                "id": 1,
                "name": "Tester"
            },
            {
                "code": "' .SiteBusinessRoleCode::SITE_MANAGER.'",
                "id": 2,
                "name": "Site manager"
            },
            {
                "code": "' .SiteBusinessRoleCode::SITE_ADMIN.'",
                "id": 3,
                "name": "Site admin"
            }
        ],
        "brakeTestType": [
            {
                "id": 1,
                "code": "' .BrakeTestTypeCode::DECELEROMETER.'",
                "name": "decelerometer"
            },
            {
                "id": 2,
                "code": "' .BrakeTestTypeCode::FLOOR.'",
                "name": "floor"
            },
            {
                "id": 3,
                "code": "' .BrakeTestTypeCode::GRADIENT.'",
                "name": "gradient"
            },
            {
                "id": 4,
                "code": "' .BrakeTestTypeCode::PLATE.'",
                "name": "plate"
            },
            {
                "id": 5,
                "code": "' .BrakeTestTypeCode::ROLLER.'",
                "name": "roller"
            }
        ],
        "equipmentModelStatus": [
            {
                "id": 1,
                "code": "'.EquipmentModelStatusCode::APPROVED.'",
                "name": "Approved"
            },
            {
                "id": 2,
                "code": "'.EquipmentModelStatusCode::NOT_INSTALLABLE.'",
                "name": "Not Installable"
            },
            {
                "id": 3,
                "code": "'.EquipmentModelStatusCode::WITHDRAWN.'",
                "name": "Withdrawn"
            }
        ],
        "personSystemRoles": {
            "1": {
                "id": 1,
                "code": "USER",
                "name": "User"
            },
            "2": {
                "id": 2,
                "code": "VEHICLE-EXAMINER",
                "name": "Vehicle Examiner"
            },
            "3": {
                "id": 3,
                "code": "DVSA-SCHEME-MANAGEMENT",
                "name": "DVSA Scheme Management"
            },
            "4": {
                "id": 4,
                "code": "DVSA-SCHEME-USER",
                "name": "DVSA Scheme User"
            },
            "5": {
                "id": 5,
                "code": "DVSA-AREA-OFFICE-1",
                "name": "DVSA Area Admin"
            },
            "6": {
                "id": 6,
                "code": "FINANCE",
                "name": "Finance"
            },
            "7": {
                "id": 7,
                "code": "CUSTOMER-SERVICE-MANAGER",
                "name": "Customer Service Manager"
            },
            "8": {
                "id": 8,
                "code": "CUSTOMER-SERVICE-CENTRE-OPERATIVE",
                "name": "Customer Service Operative"
            },
            "9": {
                "id": 9,
                "code": "CRON",
                "name": "Cron User"
            },
            "10": {
                "id": 10,
                "code": "DVLA-OPERATIVE",
                "name": "DVLA Operative"
            },
            "11": {
                "id": 11,
                "code": "DVSA-AREA-OFFICE-2",
                "name": "DVSA Area Admin 2"
            },
            "12": {
                "id": 12,
                "code": "GVTS-TESTER",
                "name": "GVTS Tester"
            },
            "13": {
                "id": 13,
                "code": "VM-10519-USER",
                "name": "VM-10519 User"
            },
            "14": {
                "id": 14,
                "code": "DVLA-MANAGER",
                "name": "DVLA Manager"
            }
        },
        "reasonsForEmptyVRM": [
            {
                "id": 1,
                "code": "MISS",
                "name": "Missing"
            },
            {
                "id": 2,
                "code": "NOTR",
                "name": "Not required"
            }
        ],
        "reasonsForEmptyVIN": [
            {
                "id": 1,
                "code": "MISS",
                "name": "Missing"
            },
            {
                "id": 2,
                "code": "NOTF",
                "name": "Not found"
            },
            {
                "id": 3,
                "code": "NOTR",
                "name": "Not required"
            }
        ],
        "qualificationStatus": [
            {
                "id": 0,
                "code": "UNKN",
                "name": "Unknown"
            },
            {
                "id": 7,
                "code": "ITRN",
                "name": "Initial Training Needed"
            },
            {
                "id": 8,
                "code": "DMTN",
                "name": "Demo Test Needed"
            },
            {
                "id": 9,
                "code": "QLFD",
                "name": "Qualified"
            },
            {
                "id": 10,
                "code": "RFSHN",
                "name": "Refresher Needed"
            },
            {
                "id": 11,
                "code": "SPND",
                "name": "Suspended"
            }
        ]
    }
}',
            true
        );

        return $result;
    }
}
