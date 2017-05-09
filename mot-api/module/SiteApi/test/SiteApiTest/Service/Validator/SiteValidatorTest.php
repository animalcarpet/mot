<?php

namespace SiteApiTest\Service\Validator;

use DvsaCommon\Dto\Contact\AddressDto;
use DvsaCommon\Dto\Contact\EmailDto;
use DvsaCommon\Dto\Contact\PhoneDto;
use DvsaCommon\Dto\Site\FacilityDto;
use DvsaCommon\Dto\Site\SiteContactDto;
use DvsaCommon\Dto\Site\VehicleTestingStationDto;
use DvsaCommon\Enum\SiteStatusCode;
use DvsaCommon\Enum\SiteTypeCode;
use DvsaCommon\Validator\EmailAddressValidator;
use DvsaCommonApi\Service\Exception\BadRequestException;
use DvsaCommonApiTest\Service\AbstractServiceTestCase;
use SiteApi\Service\Validator\SiteDetailsValidator;
use SiteApi\Service\Validator\SiteValidator;
use SiteApi\Service\Validator\TestingFacilitiesValidator;

/**
 * Testing that validator returns correct results.
 */
class SiteValidatorTest extends AbstractServiceTestCase
{
    const SITE_NAME = 'Site Name';

    /** @var SiteValidator */
    private $validator;

    public function setUp()
    {
        $this->validator = new SiteValidator(
            null,
            new TestingFacilitiesValidator(),
            new SiteDetailsValidator()
        );
    }

    /**
     * @dataProvider dataProviderTestValidator
     */
    public function testValidator($site, $errors = false)
    {
        if ($errors === true) {
            $this->setExpectedException(BadRequestException::class, 'Validation errors encountered');
        }

        $this->validator->validate($site);
    }

    public function dataProviderTestValidator()
    {
        return [
            // no errors full form
            [
                'site' => (new VehicleTestingStationDto())
                    ->setContacts(
                        [
                            (new SiteContactDto())
                                ->setAddress(
                                    (new AddressDto())
                                        ->setAddressLine1('AddressLine1')
                                        ->setAddressLine2('AddressLine2')
                                        ->setAddressLine3('AddressLine3')
                                        ->setTown('Town')
                                        ->setPostcode('Postcode')
                                )
                                ->setEmails([(new EmailDto())->setEmailConfirm('sitevalidatortest@'.EmailAddressValidator::TEST_DOMAIN)->setIsSupplied(true)->setIsPrimary(true)->setEmail('sitevalidatortest@'.EmailAddressValidator::TEST_DOMAIN)])
                                ->setPhones([(new PhoneDto())->setIsPrimary(true)->setNumber('0123456789')]),
                        ]
                    )
                    ->setStatus(SiteStatusCode::APPROVED)
                    ->setType(SiteTypeCode::VEHICLE_TESTING_STATION)
                    ->setName(self::SITE_NAME)
                    ->setTestClasses([1, 2, 3])
                    ->setFacilities([new FacilityDto()])
                    ->setIsOptlSelected(true)
                    ->setIsTptlSelected(true),
            ],
            // no errors partial form
            [
                'site' => (new VehicleTestingStationDto())
                    ->setContacts(
                        [
                            (new SiteContactDto())
                                ->setAddress(
                                    (new AddressDto())
                                        ->setAddressLine1('AddressLine1')
                                        ->setTown('Town')
                                        ->setPostcode('Postcode')
                                )
                                ->setEmails([(new EmailDto())->setEmailConfirm('sitevalidatortest@'.EmailAddressValidator::TEST_DOMAIN)->setIsSupplied(true)->setIsPrimary(true)->setEmail('sitevalidatortest@'.EmailAddressValidator::TEST_DOMAIN)])
                                ->setPhones([(new PhoneDto())->setIsPrimary(true)->setNumber('0123456789')]),
                        ]
                    )
                    ->setStatus(SiteStatusCode::APPROVED)
                    ->setType(SiteTypeCode::VEHICLE_TESTING_STATION)
                    ->setTestClasses([1, 2, 3])
                    ->setFacilities([new FacilityDto()])
                    ->setIsOptlSelected(true)
                    ->setIsTptlSelected(true),
            ],
            // no status
            [
                'site' => (new VehicleTestingStationDto())
                    ->setContacts(
                        [
                            (new SiteContactDto())
                                ->setAddress(
                                    (new AddressDto())
                                        ->setAddressLine1('AddressLine1')
                                        ->setAddressLine2('AddressLine2')
                                        ->setAddressLine3('AddressLine3')
                                        ->setTown('Town')
                                        ->setPostcode('Postcode')
                                )
                                ->setEmails([(new EmailDto())->setEmailConfirm('sitevalidatortest@'.EmailAddressValidator::TEST_DOMAIN)->setIsSupplied(true)->setIsPrimary(true)->setEmail('sitevalidatortest@'.EmailAddressValidator::TEST_DOMAIN)])
                                ->setPhones([(new PhoneDto())->setIsPrimary(true)->setNumber('0123456789')]),
                        ]
                    )
                    ->setType(SiteTypeCode::VEHICLE_TESTING_STATION)
                    ->setName(self::SITE_NAME)
                    ->setTestClasses([1, 2, 3])
                    ->setFacilities([new FacilityDto()])
                    ->setIsOptlSelected(true)
                    ->setIsTptlSelected(true),
                'errors' => true,
            ],
            // Valid No Email
            [
                'site' => (new VehicleTestingStationDto())
                    ->setContacts(
                        [
                            (new SiteContactDto())
                                ->setAddress(
                                    (new AddressDto())
                                        ->setAddressLine1('AddressLine1')
                                        ->setTown('Town')
                                        ->setPostcode('Postcode')
                                )
                                ->setEmails([(new EmailDto())->setIsSupplied(false)->setIsPrimary(true)])
                                ->setPhones([(new PhoneDto())->setIsPrimary(true)->setNumber('0123456789')]),
                        ]
                    )
                    ->setStatus(SiteStatusCode::APPROVED)
                    ->setType(SiteTypeCode::VEHICLE_TESTING_STATION)
                    ->setTestClasses([1, 2, 3])
                    ->setFacilities([new FacilityDto()])
                    ->setIsOptlSelected(true)
                    ->setIsTptlSelected(true),
            ],
            // Error no Type
            [
                'site' => (new VehicleTestingStationDto())
                    ->setContacts(
                        [
                            (new SiteContactDto())
                                ->setAddress(
                                    (new AddressDto())
                                        ->setAddressLine1('AddressLine1')
                                        ->setTown('Town')
                                        ->setPostcode('Postcode')
                                )
                                ->setEmails([(new EmailDto())->setEmailConfirm('sitevalidatortest@'.EmailAddressValidator::TEST_DOMAIN)->setIsSupplied(true)->setIsPrimary(true)->setEmail('sitevalidatortest@'.EmailAddressValidator::TEST_DOMAIN)])
                                ->setPhones([(new PhoneDto())->setIsPrimary(true)->setNumber('0123456789')]),
                        ]
                    )
                    ->setStatus(SiteStatusCode::APPROVED)
                    ->setTestClasses([1, 2, 3])
                    ->setFacilities([new FacilityDto()])
                    ->setIsOptlSelected(true)
                    ->setIsTptlSelected(true),
                'errors' => true,
            ],
            // Error invalid Type
            [
                'site' => (new VehicleTestingStationDto())
                    ->setContacts(
                        [
                            (new SiteContactDto())
                                ->setAddress(
                                    (new AddressDto())
                                        ->setAddressLine1('AddressLine1')
                                        ->setTown('Town')
                                        ->setPostcode('Postcode')
                                )
                                ->setEmails([(new EmailDto())->setEmailConfirm('sitevalidatortest@'.EmailAddressValidator::TEST_DOMAIN)->setIsSupplied(true)->setIsPrimary(true)->setEmail('sitevalidatortest@'.EmailAddressValidator::TEST_DOMAIN)])
                                ->setPhones([(new PhoneDto())->setIsPrimary(true)->setNumber('0123456789')]),
                        ]
                    )
                    ->setStatus(SiteStatusCode::APPROVED)
                    ->setType('blue')
                    ->setTestClasses([1, 2, 3])
                    ->setFacilities([new FacilityDto()])
                    ->setIsOptlSelected(true)
                    ->setIsTptlSelected(true),
                'errors' => true,
            ],
            // Error no Address
            [
                'site' => (new VehicleTestingStationDto())
                    ->setContacts(
                        [
                            (new SiteContactDto())
                                ->setAddress(new AddressDto())
                                ->setEmails([(new EmailDto())->setEmailConfirm('sitevalidatortest@'.EmailAddressValidator::TEST_DOMAIN)->setIsSupplied(true)->setIsPrimary(true)->setEmail('sitevalidatortest@'.EmailAddressValidator::TEST_DOMAIN)])
                                ->setPhones([(new PhoneDto())->setIsPrimary(true)->setNumber('0123456789')]),
                        ]
                    )
                    ->setStatus(SiteStatusCode::APPROVED)
                    ->setType(SiteTypeCode::VEHICLE_TESTING_STATION)
                    ->setTestClasses([1, 2, 3])
                    ->setFacilities([new FacilityDto()])
                    ->setIsOptlSelected(true)
                    ->setIsTptlSelected(true),
                'errors' => true,
            ],
            // Error no Telephone
            [
                'site' => (new VehicleTestingStationDto())
                    ->setContacts(
                        [
                            (new SiteContactDto())
                                ->setAddress(
                                    (new AddressDto())
                                        ->setAddressLine1('AddressLine1')
                                        ->setTown('Town')
                                        ->setPostcode('Postcode')
                                )
                                ->setEmails([(new EmailDto())->setEmailConfirm('sitevalidatortest@'.EmailAddressValidator::TEST_DOMAIN)->setIsSupplied(true)->setIsPrimary(true)->setEmail('sitevalidatortest@'.EmailAddressValidator::TEST_DOMAIN)]),
                        ]
                    )
                    ->setStatus(SiteStatusCode::APPROVED)
                    ->setType(SiteTypeCode::VEHICLE_TESTING_STATION)
                    ->setTestClasses([1, 2, 3])
                    ->setFacilities([new FacilityDto()])
                    ->setIsOptlSelected(true)
                    ->setIsTptlSelected(true),
                'errors' => true,
            ],
            // Error no email
            [
                'site' => (new VehicleTestingStationDto())
                    ->setContacts(
                        [
                            (new SiteContactDto())
                                ->setAddress(
                                    (new AddressDto())
                                        ->setAddressLine1('AddressLine1')
                                        ->setTown('Town')
                                        ->setPostcode('Postcode')
                                )
                                ->setEmails([(new EmailDto())->setIsSupplied(true)->setIsPrimary(true)])
                                ->setPhones([(new PhoneDto())->setIsPrimary(true)->setNumber('0123456789')]),
                        ]
                    )
                    ->setStatus(SiteStatusCode::APPROVED)
                    ->setType(SiteTypeCode::VEHICLE_TESTING_STATION)
                    ->setTestClasses([1, 2, 3])
                    ->setFacilities([new FacilityDto()])
                    ->setIsOptlSelected(true)
                    ->setIsTptlSelected(true),
                'errors' => true,
            ],
            // Error email invalid
            [
                'site' => (new VehicleTestingStationDto())
                    ->setContacts(
                        [
                            (new SiteContactDto())
                                ->setAddress(
                                    (new AddressDto())
                                        ->setAddressLine1('AddressLine1')
                                        ->setTown('Town')
                                        ->setPostcode('Postcode')
                                )
                                ->setEmails([(new EmailDto())->setEmailConfirm('sitevalidatortest')->setIsSupplied(true)->setIsPrimary(true)->setEmail('sitevalidatortest')])
                                ->setPhones([(new PhoneDto())->setIsPrimary(true)->setNumber('0123456789')]),
                        ]
                    )
                    ->setStatus(SiteStatusCode::APPROVED)
                    ->setType(SiteTypeCode::VEHICLE_TESTING_STATION)
                    ->setTestClasses([1, 2, 3])
                    ->setFacilities([new FacilityDto()])
                    ->setIsOptlSelected(true)
                    ->setIsTptlSelected(true),
                'errors' => true,
            ],
            // Error email different
            [
                'site' => (new VehicleTestingStationDto())
                    ->setContacts(
                        [
                            (new SiteContactDto())
                                ->setAddress(
                                    (new AddressDto())
                                        ->setAddressLine1('AddressLine1')
                                        ->setTown('Town')
                                        ->setPostcode('Postcode')
                                )
                                ->setEmails([(new EmailDto())->setEmailConfirm('sitevalidatortest1@'.EmailAddressValidator::TEST_DOMAIN)->setIsSupplied(true)->setIsPrimary(true)->setEmail('sitevalidatortest@'.EmailAddressValidator::TEST_DOMAIN)])
                                ->setPhones([(new PhoneDto())->setIsPrimary(true)->setNumber('0123456789')]),
                        ]
                    )
                    ->setStatus(SiteStatusCode::APPROVED)
                    ->setType(SiteTypeCode::VEHICLE_TESTING_STATION)
                    ->setTestClasses([1, 2, 3])
                    ->setIsOptlSelected(true)
                    ->setIsTptlSelected(true),
                'errors' => true,
            ],
            // errors no Optl selected
            [
                'organisation' => (new VehicleTestingStationDto())
                    ->setContacts(
                        [
                            (new SiteContactDto())
                                ->setAddress(
                                    (new AddressDto())
                                        ->setAddressLine1('AddressLine1')
                                        ->setTown('Town')
                                        ->setPostcode('Postcode')
                                )
                                ->setEmails([(new EmailDto())->setEmailConfirm('sitevalidatortest@'.EmailAddressValidator::TEST_DOMAIN)->setIsSupplied(true)->setIsPrimary(true)->setEmail('sitevalidatortest@'.EmailAddressValidator::TEST_DOMAIN)])
                                ->setPhones([(new PhoneDto())->setIsPrimary(true)->setNumber('0123456789')]),
                        ]
                    )
                    ->setType(SiteTypeCode::VEHICLE_TESTING_STATION)
                    ->setTestClasses([1, 2, 3])
                    ->setFacilities([new FacilityDto()])
                    ->setIsOptlSelected(false)
                    ->setIsTptlSelected(true),
                'errors' => true,
            ],
            // errors no Tptl selected
            [
                'organisation' => (new VehicleTestingStationDto())
                    ->setContacts(
                        [
                            (new SiteContactDto())
                                ->setAddress(
                                    (new AddressDto())
                                        ->setAddressLine1('AddressLine1')
                                        ->setTown('Town')
                                        ->setPostcode('Postcode')
                                )
                                ->setEmails([(new EmailDto())->setEmailConfirm('sitevalidatortest@'.EmailAddressValidator::TEST_DOMAIN)->setIsSupplied(true)->setIsPrimary(true)->setEmail('sitevalidatortest@'.EmailAddressValidator::TEST_DOMAIN)])
                                ->setPhones([(new PhoneDto())->setIsPrimary(true)->setNumber('0123456789')]),
                        ]
                    )
                    ->setStatus(SiteStatusCode::APPROVED)
                    ->setType(SiteTypeCode::VEHICLE_TESTING_STATION)
                    ->setTestClasses([1, 2, 3])
                    ->setFacilities([new FacilityDto()])
                    ->setIsOptlSelected(true)
                    ->setIsTptlSelected(false),
                'errors' => true,
            ],
            // errors no lane selected
            [
                'organisation' => (new VehicleTestingStationDto())
                    ->setContacts(
                        [
                            (new SiteContactDto())
                                ->setAddress(
                                    (new AddressDto())
                                        ->setAddressLine1('AddressLine1')
                                        ->setTown('Town')
                                        ->setPostcode('Postcode')
                                )
                                ->setEmails([(new EmailDto())->setEmailConfirm('sitevalidatortest@'.EmailAddressValidator::TEST_DOMAIN)->setIsSupplied(true)->setIsPrimary(true)->setEmail('sitevalidatortest@'.EmailAddressValidator::TEST_DOMAIN)])
                                ->setPhones([(new PhoneDto())->setIsPrimary(true)->setNumber('0123456789')]),
                        ]
                    )
                    ->setStatus(SiteStatusCode::APPROVED)
                    ->setType(SiteTypeCode::VEHICLE_TESTING_STATION)
                    ->setTestClasses([1, 2, 3])
                    ->setIsOptlSelected(true)
                    ->setIsTptlSelected(true),
                'errors' => true,
            ],
        ];
    }
}
