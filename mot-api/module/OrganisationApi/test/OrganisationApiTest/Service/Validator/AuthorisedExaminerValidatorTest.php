<?php

namespace OrganisationApiTest\Service\Validator;

use DvsaCommon\Dto\Contact\AddressDto;
use DvsaCommon\Dto\Contact\EmailDto;
use DvsaCommon\Dto\Contact\PhoneDto;
use DvsaCommon\Dto\Organisation\AuthorisedExaminerAuthorisationDto;
use DvsaCommon\Dto\Organisation\OrganisationContactDto;
use DvsaCommon\Dto\Organisation\OrganisationDto;
use DvsaCommon\Enum\CompanyTypeCode;
use DvsaCommon\Validator\EmailAddressValidator;
use DvsaCommonApi\Service\Exception\BadRequestException;
use OrganisationApi\Service\Validator\AuthorisedExaminerValidator;

/**
 * Class AuthorisedExaminerValidatorTest.
 */
class AuthorisedExaminerValidatorTest extends \PHPUnit_Framework_TestCase
{
    const AE_NAME = 'AE Name';

    /** @var AuthorisedExaminerValidator */
    private $validator;

    public function setUp()
    {
        $this->validator = new AuthorisedExaminerValidator();
    }

    /**
     * @dataProvider dataProviderTestValidator
     */
    public function testValidator($organisation, $errors = false)
    {
        if ($errors === true) {
            $this->setExpectedException(BadRequestException::class, 'Validation errors encountered');
        }

        $this->validator->validate($organisation, $this->fakedAreaOfficeList());
    }

    protected function fakedAreaOfficeList()
    {
        return [
            [
                'id' => '3000',
                'name' => 'Area Office 01',
                'siteNumber' => '01FOO',
                'areaOfficeNumber' => '01',
            ],
            [
                'id' => '3001',
                'name' => 'Area Office 02',
                'siteNumber' => '02BAR',
                'areaOfficeNumber' => '02',
            ],
        ];
    }

    public function dataProviderTestValidator()
    {
        $authForAeDto = new AuthorisedExaminerAuthorisationDto();
        $authForAeDto->setAssignedAreaOffice(1);

        $invalidAuthForAeDto = new AuthorisedExaminerAuthorisationDto();
        $invalidAuthForAeDto->setAssignedAreaOffice(999);

        return [
            // no errors
            [
                'organisation' => (new OrganisationDto())
                    ->setAuthorisedExaminerAuthorisation($authForAeDto)
                    ->setContacts(
                        [
                            (new OrganisationContactDto())
                                ->setAddress(
                                    (new AddressDto())
                                        ->setAddressLine1('AddressLine1')
                                        ->setTown('Town')
                                        ->setPostcode('Postcode')
                                )
                                ->setEmails([(new EmailDto())->setIsPrimary(true)->setEmail('authorisedexaminervalidatortest@'.EmailAddressValidator::TEST_DOMAIN)])
                                ->setPhones([(new PhoneDto())->setIsPrimary(true)->setNumber('0123456789')]),
                        ]
                    )
                    ->setName(self::AE_NAME)
                    ->setCompanyType(CompanyTypeCode::SOLE_TRADER),
            ],
            // Valid No Email
            [
                'organisation' => (new OrganisationDto())
                    ->setAuthorisedExaminerAuthorisation($authForAeDto)
                    ->setContacts(
                        [
                            (new OrganisationContactDto())
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
                    ->setName(self::AE_NAME)
                    ->setCompanyType(CompanyTypeCode::SOLE_TRADER),
            ],
            // Error no Name
            [
                'organisation' => (new OrganisationDto())
                    ->setAuthorisedExaminerAuthorisation($authForAeDto)
                    ->setContacts(
                        [
                            (new OrganisationContactDto())
                                ->setAddress(
                                    (new AddressDto())
                                        ->setAddressLine1('AddressLine1')
                                        ->setTown('Town')
                                        ->setPostcode('Postcode')
                                )
                                ->setEmails([(new EmailDto())->setIsPrimary(true)->setEmail('dummy@dummy.com')])
                                ->setPhones([(new PhoneDto())->setIsPrimary(true)->setNumber('0123456789')]),
                        ]
                    )
                    ->setCompanyType(CompanyTypeCode::SOLE_TRADER),
                'errors' => true,
            ],
            // Error no Type
            [
                'organisation' => (new OrganisationDto())
                    ->setAuthorisedExaminerAuthorisation($authForAeDto)
                    ->setContacts(
                        [
                            (new OrganisationContactDto())
                                ->setAddress(
                                    (new AddressDto())
                                        ->setAddressLine1('AddressLine1')
                                        ->setTown('Town')
                                        ->setPostcode('Postcode')
                                )
                                ->setEmails([(new EmailDto())->setIsPrimary(true)->setEmail('dummy@dummy.com')])
                                ->setPhones([(new PhoneDto())->setIsPrimary(true)->setNumber('0123456789')]),
                        ]
                    )
                    ->setName(self::AE_NAME),
                'errors' => true,
            ],
            // Error no Address
            [
                'organisation' => (new OrganisationDto())
                    ->setAuthorisedExaminerAuthorisation($authForAeDto)
                    ->setContacts(
                        [
                            (new OrganisationContactDto())
                                ->setAddress((new AddressDto()))
                                ->setEmails([(new EmailDto())->setIsPrimary(true)->setEmail('dummy@dummy.com')])
                                ->setPhones([(new PhoneDto())->setIsPrimary(true)->setNumber('0123456789')]),
                        ]
                    )
                    ->setName(self::AE_NAME)
                    ->setCompanyType(CompanyTypeCode::SOLE_TRADER),
                'errors' => true,
            ],
            // Error no Telephone
            [
                'organisation' => (new OrganisationDto())
                    ->setAuthorisedExaminerAuthorisation($authForAeDto)
                    ->setContacts(
                        [
                            (new OrganisationContactDto())
                                ->setAddress(
                                    (new AddressDto())
                                        ->setAddressLine1('AddressLine1')
                                        ->setTown('Town')
                                        ->setPostcode('Postcode')
                                )
                                ->setEmails([(new EmailDto())->setIsPrimary(true)->setEmail('dummy@dummy.com')]),
                        ]
                    )
                    ->setName(self::AE_NAME)
                    ->setCompanyType(CompanyTypeCode::SOLE_TRADER),
                'errors' => true,
            ],
            // Error email invalid
            [
                'organisation' => (new OrganisationDto())
                    ->setAuthorisedExaminerAuthorisation($authForAeDto)
                    ->setContacts(
                        [
                            (new OrganisationContactDto())
                                ->setAddress(
                                    (new AddressDto())
                                        ->setAddressLine1('AddressLine1')
                                        ->setTown('Town')
                                        ->setPostcode('Postcode')
                                )
                                ->setEmails([(new EmailDto())->setIsPrimary(true)->setIsSupplied(true)->setEmail('invalidEmail')])
                                ->setPhones([(new PhoneDto())->setIsPrimary(true)->setNumber('0123456789')]),
                        ]
                    )
                    ->setName(self::AE_NAME)
                    ->setCompanyType(CompanyTypeCode::SOLE_TRADER),
                'errors' => true,
            ],
            // Error Company Registration Number Required
            [
                'organisation' => (new OrganisationDto())
                    ->setAuthorisedExaminerAuthorisation($authForAeDto)
                    ->setContacts(
                        [
                            (new OrganisationContactDto())
                                ->setAddress(
                                    (new AddressDto())
                                        ->setAddressLine1('AddressLine1')
                                        ->setTown('Town')
                                        ->setPostcode('Postcode')
                                )
                                ->setEmails([(new EmailDto())->setIsPrimary(true)->setEmail('invalidEmail')])
                                ->setPhones([(new PhoneDto())->setIsPrimary(true)->setNumber('0123456789')]),
                        ]
                    )
                    ->setName(self::AE_NAME)
                    ->setCompanyType(CompanyTypeCode::COMPANY)
                    ->setRegisteredCompanyNumber(''),
                'errors' => true,
            ],
            // Error Invalid Company Registration Number
            [
                'organisation' => (new OrganisationDto())
                    ->setAuthorisedExaminerAuthorisation($authForAeDto)
                    ->setContacts(
                        [
                            (new OrganisationContactDto())
                                ->setAddress(
                                    (new AddressDto())
                                        ->setAddressLine1('AddressLine1')
                                        ->setTown('Town')
                                        ->setPostcode('Postcode')
                                )
                                ->setEmails([(new EmailDto())->setIsPrimary(true)->setEmail('invalidEmail')])
                                ->setPhones([(new PhoneDto())->setIsPrimary(true)->setNumber('0123456789')]),
                        ]
                    )
                    ->setName(self::AE_NAME)
                    ->setCompanyType(CompanyTypeCode::COMPANY)
                    ->setRegisteredCompanyNumber('AAA1111'),
                'errors' => true,
            ],
            // Error Company Number wrong length
            [
                'organisation' => (new OrganisationDto())
                    ->setAuthorisedExaminerAuthorisation($authForAeDto)
                    ->setContacts(
                        [
                            (new OrganisationContactDto())
                                ->setAddress(
                                    (new AddressDto())
                                        ->setAddressLine1('AddressLine1')
                                        ->setTown('Town')
                                        ->setPostcode('Postcode')
                                )
                                ->setEmails([(new EmailDto())->setIsPrimary(true)->setEmail('invalidEmail')])
                                ->setPhones([(new PhoneDto())->setIsPrimary(true)->setNumber('0123456789')]),
                        ]
                    )
                    ->setName(self::AE_NAME)
                    ->setCompanyType(CompanyTypeCode::COMPANY)
                    ->setRegisteredCompanyNumber('123456'),
                'errors' => true,
            ],
            // Area Office number required
            [
                'organisation' => (new OrganisationDto())
                    ->setContacts(
                        [
                            (new OrganisationContactDto())
                                ->setAddress(
                                    (new AddressDto())
                                        ->setAddressLine1('AddressLine1')
                                        ->setTown('Town')
                                        ->setPostcode('Postcode')
                                )
                                ->setEmails([(new EmailDto())->setIsPrimary(true)->setEmail('invalidEmail')])
                                ->setPhones([(new PhoneDto())->setIsPrimary(true)->setNumber('0123456789')]),
                        ]
                    )
                    ->setName(self::AE_NAME)
                    ->setCompanyType(CompanyTypeCode::COMPANY)
                    ->setRegisteredCompanyNumber(''),
                'errors' => true,
            ],
            // A *valid* Area Office number required
            [
                'organisation' => (new OrganisationDto())
                    ->setAuthorisedExaminerAuthorisation($invalidAuthForAeDto)
                    ->setContacts(
                        [
                            (new OrganisationContactDto())
                                ->setAddress(
                                    (new AddressDto())
                                        ->setAddressLine1('AddressLine1')
                                        ->setTown('Town')
                                        ->setPostcode('Postcode')
                                )
                                ->setEmails([(new EmailDto())->setIsPrimary(true)->setEmail('invalidEmail')])
                                ->setPhones([(new PhoneDto())->setIsPrimary(true)->setNumber('0123456789')]),
                        ]
                    )
                    ->setName(self::AE_NAME)
                    ->setCompanyType(CompanyTypeCode::COMPANY)
                    ->setRegisteredCompanyNumber(''),
                'errors' => true,
            ],
        ];
    }
}
