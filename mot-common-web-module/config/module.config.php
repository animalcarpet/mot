<?php
return [
    'view_manager' => [
        'template_map'        => [
            'errorMessages'                       => __DIR__ . '/../view/partials/errorMessages.phtml',
            'zendFormErrorMessages'               => __DIR__ . '/../view/partials/zendFormErrorMessages.phtml',
            'infoMessages'                        => __DIR__ . '/../view/partials/infoMessages.phtml',
            'validationMessages'                  => __DIR__ . '/../view/partials/validationMessages.phtml',
            'actionNavigator'                     => __DIR__ . '/../view/partials/action-navigator.phtml',
            'actionLinkNavigator'                 => __DIR__ . '/../view/partials/action-link-navigator.phtml',
            'commonDataTable'                     => __DIR__ . '/../view/partials/data-table.phtml',

            'inputElement'                        => __DIR__ . '/../view/partials/form/input-element.phtml',
            'hiddenElement'                       => __DIR__ . '/../view/partials/form/hidden-element.phtml',
            'checkboxElement'                     => __DIR__ . '/../view/partials/form/checkbox-element.phtml',
            'checkboxBox'                         => __DIR__ . '/../view/partials/form/checkbox-box.phtml',
            'radioElement'                        => __DIR__ . '/../view/partials/form/radio-element.phtml',
            'selectElement'                       => __DIR__ . '/../view/partials/form/select-element.phtml',
            'selectElementSelectedByDisplayedValue' => __DIR__ . '/../view/partials/form/select-element-selected-by-displayed-value.phtml',
            'selectEnumType2Element'              => __DIR__ . '/../view/partials/form/select-enum-type-2-element.phtml',
            'selectElementExtended'               => __DIR__ . '/../view/partials/form/select-element-extended.phtml',
            'submitElement'                       => __DIR__ . '/../view/partials/form/submit-element.phtml',
            'datePickerBox'                       => __DIR__ . '/../view/partials/form/date-picker-box.phtml',
            'dateGroup'                           => __DIR__ . '/../view/partials/form/date-group.phtml',
            'drivingLicenceInput'                 => __DIR__ . '/../view/partials/form/driving-licence-input.phtml',
            'genderBox'                           => __DIR__ . '/../view/partials/form/gender-box.phtml',
            'inputBox'                            => __DIR__ . '/../view/partials/form/input-box.phtml',
            'inspectionLocation'                  => __DIR__ . '/../view/partials/form/inspection-location.phtml',
            'passwordBox'                         => __DIR__ . '/../view/partials/form/password-box.phtml',
            'selectBox'                           => __DIR__ . '/../view/partials/form/select-box.phtml',
            'submitBox'                           => __DIR__ . '/../view/partials/form/submit-box.phtml',
            'titleBox'                            => __DIR__ . '/../view/partials/form/title-box.phtml',
            'doubleRadioBox'                      => __DIR__ . '/../view/partials/form/double-radio-box.phtml',
            'tripleRadioBox'                      => __DIR__ . '/../view/partials/form/triple-radio-box-vertical.phtml',
            'checkboxEnrichedElement'             => __DIR__ . '/../view/partials/form/checkbox-enriched-element.phtml',
            'textareaElement'                     => __DIR__ . '/../view/partials/form/textarea-element.phtml',
            'fieldErrorMessage'                   => __DIR__ . '/../view/partials/form/field-error-message.phtml',

            'authorisedExaminer/businessDetails' => __DIR__
                . '/../view/partials/form/authorised-examiner/business-details.phtml',
            'authorisedExaminer/businessAddress' => __DIR__
                . '/../view/partials/form/authorised-examiner/business-address.phtml',

            'personalDetailsFragment'             => __DIR__
                . '/../view/partials/form/fragments/personal-details.phtml',
            'addressDetailsFragment'              => __DIR__
                . '/../view/partials/form/fragments/address-details.phtml',
            'addressAndContactDetailsFragment'    => __DIR__
                . '/../view/partials/form/fragments/address-and-contact-details.phtml',
            'authorisedExaminerAddressAndContactDetailsFragment'    => __DIR__
                . '/../view/partials/form/fragments/authorised-examiner-address-and-contact-details.phtml',

            'personalDetailsReview'               => __DIR__
                . '/../view/partials/reviews/personal-details-review.phtml',

            'breadcrumb'                         => __DIR__ . '/../view/partials/breadcrumb.phtml',
        ],
        'template_path_stack' => [
            __DIR__ . '/../view',
        ],
    ],
    'view_helpers' => [
        'invokables' => [
            'withDefault' => 'Dvsa\Mot\Frontend\ViewHelper\WithDefault',
            'truncate'    => 'Dvsa\Mot\Frontend\ViewHelper\Truncate',
            'htmlId'      => 'Dvsa\Mot\Frontend\ViewHelper\HTMLId',
            'catalogGetIdByValue' => 'Dvsa\Mot\Frontend\ViewHelper\CatalogGetIdByValue',
            'dateRange' => 'Dvsa\Mot\Frontend\ViewHelper\DateRange',
            'breadCrumb' => 'Dvsa\Mot\Frontend\ViewHelper\BreadCrumb',
        ],
    ],
];
