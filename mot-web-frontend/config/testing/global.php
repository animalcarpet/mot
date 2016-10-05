<?php

return [
    'dvsa_authentication' => [
        'openAM' => [
            /*
             * The adapter used to talk to the OpenAM REST API.
             *
             * For SSL Connections to OpenAM we need to use Curl: 'Zend\Http\Client\Adapter\Curl'.
             */
            'adapter' => 'Zend\Http\Client\Adapter\Socket',
            /*
             * Username of the OpenAM admin account.
             */
            'admin_login' => 'amadmin',
            /*
             * Password of the OpenAM admin account.
             */
            'admin_password' => 'cangetinam',
            /*
             * Name of the cookie where the OpenAM session token is kept.
             */
            'cookie_name' => 'iPlanetDirectoryPro',
            /*
             * Path for OpenAM cookies.
             */
            'cookie_path' => '/',
            /*
             * Domain for OpenAM cookies.
             */
            'cookie_domain' => '.motdev.org.uk',
            /*
             * Secure flag value for OpenAM cookies.
             */
            'cookie_secure' => false,
            /*
             * Secure flag value for OpenAM cookies.
             */
            'cookie_http_only' => true,
            /*
             * Identity attribute name under which username is stored.
             */
            'identity_attribute_username' => 'uid',
            /*
             * Identity attribute name under which OpenAM entryuuid is stored.
             */
            'identity_attribute_uuid' => 'entryuuid',
            /*
             * Logout URL to logout of the application through OpenAM. Has a goto parameter that tells OpenAM where to
             * redirect after logout. Web Frontend only.
             */
            'logout_url' => 'https://dev.motdev.org.uk/secure/UI/Login?realm=mot&goto=',
            /*
             * Lowest level domain name of the OpenAM instance. Web Frontend only.
             */
            'lowest_level_domain' => 'openam',
            /*
             * Realm that will be used when adding new accounts.
             */
            'realm' => 'mot',
            /*
             * Name of the header/token that is used when calling OpenAM.
             */
            'token_field' => 'iPlanetDirectoryPro',
            /*
             * Base URL of the OpenAM REST API.
             */
            'url' => 'http://openam.motdev.org.uk:8080/sso/',
        ],
        'whiteList' => [
            '|^/forgotten-password(?!/update$)(.*)|'
        ]
    ],
    'gaTrackingCode' => 'UA-52765902-9',
    'security' => [
        'obfuscate' => [
            'key' => 'acjdajsd92md09282822',
            'entries' => [
                'vehicleId' => true,
             ],
        ],
    ],
    'showErrorsInFrontEnd' => (getenv('APPLICATION_ENV') === 'development'),

    // HTMLPurifier
    'soflomo_purifier' => [
        'config'          => [
            'Cache.SerializerPath'  => sys_get_temp_dir(),
        ],
    ],

    // Expiration in seconds of the password reset email
    // TO CHANGE In both FrontEnd And API
    'password_reset' => [
        'expireTime' => 5400,
    ],

    // Helpdesk name and contact details (MUST BE CHANGE in both FrontEnd and Api)
    'helpdesk' => [
        'name'        => 'DVSA Helpdesk',
        'phoneNumber' => '0330 123 5654',
        'openingHrs'  => '8:30am to 5:30pm',
        'openingHrsWeekdays' => 'Monday to Friday, 8:00am to 8:00pm',
        'openingHrsSaturday' => 'Saturday, 8:00am to 2:00pm',
        'openingHrsSunday' => 'Sunday, closed'
    ],
    // merged from feature toggles config file
    'feature_toggle' => [
        'openam.password.expiry.enabled' => false,
        'vts.risk.score'                 => true,
        '2fa.method.visible'             => false,
        'infinity.contingency'           => true,
        'new_person_profile'             => true,
        '2fa.enabled'                    => false,
    ],
    'baseUrl' => 'https://dev.motdev.org.uk',
    // URL to DVSA REST API with slash (/) at the end
    'apiUrl' => 'http://dev2:80/',
    'DvsaApplicationLogger' => [
        'registerExceptionHandler' => true,
        'writers' => [
            'web-frontend-flat-file' => [
                'adapter' => '\Zend\Log\Writer\Stream',
                'options' => [
                    'output' => __DIR__ . '/../../log/mot-webfrontend.log',
                ],
                'filter' => \Zend\Log\Logger::ERR,
                'enabled' => true
            ],
        ]
    ],
    'showErrorsInFrontEnd' => (getenv('APPLICATION_ENV') === 'development'),
    'idapUrl' => "http://mot-web-frontend/stub-idap/start",
    'password_expiry_grace_period' => "30 days",
    /*
     * Relative or absolute paths to documents (manuals and guides) linked to in the application.
     */
    'manuals' => [
        [
            'name'      => 'Manual for class 1 and 2 vehicles',
            'url'       => '/documents/manuals/m1i00000001.htm',
        ],
        [
            'name'      => 'Manual for class 3, 4, 5, and 7 vehicles',
            'url'       => '/documents/manuals/m4i00000001.htm',
        ],
        [
            'name'      => 'MOT testing guide',
            'url'       => '/documents/manuals/tgi00000001.htm',
        ]
    ],
    'resources' => [
        [
            'name'      => 'Checklist for class 1 and 2 vehicles (VT29M)',
            'url'       => 'https://www.gov.uk/government/uploads/system/uploads/attachment_data/file/212494/Plain_Paper_MOT_Inspection_Checklist_for_Motorcycles__VT_29M_.pdf',
            'help_text' => '(PDF, 653 KB)',
        ],
        [
            'name'      => 'Checklist for class 3, 4, 5, and 7 vehicles (VT29)',
            'url'       => 'https://www.gov.uk/government/uploads/system/uploads/attachment_data/file/212475/Plain_Paper_MOT_Inspection_Checklist__VT_29_.pdf',
            'help_text' => '(PDF, 645 KB)',
        ],
        [
            'name'      => 'In service exhaust emission standards for road vehicles: 18th edition',
            'url'       => 'https://www.gov.uk/government/uploads/system/uploads/attachment_data/file/348035/18th-edition-emissions-book-complete.pdf',
            'help_text' => '(PDF, 837 KB)',
        ],
        [
            'name'      => 'Special notices',
            'url'       => 'https://www.gov.uk/topic/mot/manuals#special-notices',
            'help_text' => '',
        ],
    ],

    /*
    * Settings for Jasper async
    */
    'cache' => [
        'instance' => 'doctrine.cache.apc',
    ],
    'rest_client' => [
        'cache' => [
            'enabled' => false,
            'mot-test' => [
                'lifetime' => 7200
            ],
            'person' => [
                'lifetime' => 7200
            ],
            'site-name' => [
                'lifetime' => 14400
            ],
            'organisation-name' => [
                'lifetime' => 14400
            ],
        ]
    ],
    /*
    * Settings for pagination in the list of certificates
    */
    'recent_certificate_list' => [
        'page_size' => 20
    ],
    /*
    * Settings for VTS risk assessment score
    */
    'site_assessment' => [
        'green' => ['start' => 0, 'end' => 324.10],
        'amber' => ['start' => 324.11, 'end' => 459.20],
        'red' => ['start' => 459.21, 'end' => 999.99],
    ],
    //how many days before password expires notifications are going to be sent
    'password_expiry_notification_days' => [7, 3, 2, 1],
    'pdf' => [
        'mot_checklist' => [
            'templates' => [
                'motorbike' => '/opt/dvsa/mot-web-frontend/pdf/mot_checklist/templates/motobike.pdf',
                'car' => '/opt/dvsa/mot-web-frontend/pdf/mot_checklist/templates/car.pdf',
            ],
            'fonts' => [
                'monospaced' => '/opt/dvsa/mot-web-frontend/pdf/mot_checklist/fonts/courier.ttf',
            ],
        ],
    ],

];
