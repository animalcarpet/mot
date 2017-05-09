<?php

namespace OrganisationApiTest\Service;

use Doctrine\ORM\EntityManager;
use DvsaCommon\Auth\PermissionAtOrganisation;
use DvsaCommon\Enum\AuthorisationForAuthorisedExaminerStatusCode;
use DvsaCommon\Enum\CompanyTypeCode;
use DvsaCommon\Enum\EventTypeCode;
use DvsaCommon\Exception\UnauthorisedException;
use DvsaCommon\Model\AuthorisedExaminerPatchModel;
use DvsaCommon\Utility\ArrayUtils;
use DvsaCommonApi\Service\Exception\BadRequestException;
use DvsaCommonApi\Service\Exception\ServiceException;
use DvsaCommonApiTest\Service\AbstractServiceTestCase;
use DvsaCommonApiTest\Stub\ApiIdentityProviderStub;
use DvsaCommonApiTest\Stub\IdentityStub;
use DvsaCommonTest\Mocking\Repository\OrganisationContactTypeRepositoryFake;
use DvsaCommonTest\Mocking\Repository\PhoneContactTypeRepositoryFake;
use DvsaCommonTest\TestUtils\Auth\AuthorisationServiceMock;
use DvsaCommonTest\TestUtils\EntityManagerSpy;
use DvsaCommonTest\TestUtils\MethodSpy;
use DvsaCommonTest\TestUtils\XMock;
use DvsaEntities\Entity\Address;
use DvsaEntities\Entity\AuthForAeStatus;
use DvsaEntities\Entity\AuthorisationForAuthorisedExaminer;
use DvsaEntities\Entity\CompanyType;
use DvsaEntities\Entity\ContactDetail;
use DvsaEntities\Entity\Email;
use DvsaEntities\Entity\Organisation;
use DvsaEntities\Entity\OrganisationContactType;
use DvsaEntities\Entity\Phone;
use DvsaEntities\Entity\Site;
use DvsaEntities\Repository\AuthForAeStatusRepository;
use DvsaEntities\Repository\CompanyTypeRepository;
use DvsaEntities\Repository\OrganisationContactTypeRepository;
use DvsaEntities\Repository\OrganisationRepository;
use DvsaEntities\Repository\SiteRepository;
use DvsaEventApi\Service\EventService;
use DvsaEventApiTest\EventServiceSpy;
use OrganisationApi\Service\UpdateAeDetailsService;
use OrganisationApi\Service\Validator\UpdateProperty\AeAddressValidator;
use OrganisationApi\Service\Validator\UpdateProperty\AeEmailValidator;
use OrganisationApi\Service\Validator\UpdateProperty\AePhoneValidator;

/**
 * Class UpdateOrganisationDetailTest.
 */
class UpdateAeDetailsServiceTest extends AbstractServiceTestCase
{
    /** @var UpdateAeDetailsService */
    private $updateAeService;

    /** @var MethodSpy */
    private $eventServiceSpy;

    /** @var AuthorisationServiceMock */
    private $authorisationService;

    private $aeId = 192;

    /** @var OrganisationRepository */
    private $organisationRepository;

    /** @var OrganisationContactTypeRepository */
    private $organisationContactTypeRepository;

    /** @var MethodSpy */
    private $organisationRepositoryPersistSpy;

    /** @var EntityManagerSpy */
    private $entityManagerSpy;

    /** @var CompanyTypeRepository */
    private $companyTypeRepository;

    /** @var SiteRepository */
    private $siteRepository;

    /** @var EntityManager */
    private $entityManager;

    /** @var Organisation */
    private $authorisedExaminer;

    /** @var Phone */
    private $oldPrimaryPhone1;

    /** @var Phone */
    private $oldPrimaryPhone2;

    /** @var Email */
    private $oldPrimaryEmail1;

    /** @var Email */
    private $oldPrimaryEmail2;

    /** @var AuthForAeStatusRepository */
    private $authForAeStatusRepository;

    private $username = 'johny001';

    private $aeNumber = 'AE0105';

    private $aeName = 'Smart industries';

    private $companyNumber = 100200300;

    private $areaOfficeNumber = 1;

    public function setUp()
    {
        $this->organisationContactTypeRepository = new OrganisationContactTypeRepositoryFake();
        /* @var EntityManager|\PHPUnit_Framework_MockObject_MockObject $entityManager */
        $this->entityManager = XMock::of(EntityManager::class);

        $this->entityManagerSpy = new EntityManagerSpy($this->entityManager);
        /** @var EventService|\PHPUnit_Framework_MockObject_MockObject $eventService */
        $eventService = XMock::of(EventService::class);
        $this->eventServiceSpy = new MethodSpy($eventService, 'addOrganisationEvent');
        $this->authorisationService = new AuthorisationServiceMock();
        $this->authorisationService->grantedAtOrganisation(PermissionAtOrganisation::AE_UPDATE_REGISTERED_OFFICE_ADDRESS, $this->aeId);
        $this->authorisationService->grantedAtOrganisation(PermissionAtOrganisation::AE_UPDATE_CORRESPONDENCE_ADDRESS, $this->aeId);
        $this->authorisationService->grantedAtOrganisation(PermissionAtOrganisation::AE_UPDATE_REGISTERED_OFFICE_PHONE, $this->aeId);
        $this->authorisationService->grantedAtOrganisation(PermissionAtOrganisation::AE_UPDATE_CORRESPONDENCE_PHONE, $this->aeId);
        $this->authorisationService->grantedAtOrganisation(PermissionAtOrganisation::AE_UPDATE_REGISTERED_OFFICE_EMAIL, $this->aeId);
        $this->authorisationService->grantedAtOrganisation(PermissionAtOrganisation::AE_UPDATE_CORRESPONDENCE_EMAIL, $this->aeId);
        $this->authorisationService->grantedAtOrganisation(PermissionAtOrganisation::AE_UPDATE_NAME, $this->aeId);
        $this->authorisationService->grantedAtOrganisation(PermissionAtOrganisation::AE_UPDATE_TRADING_NAME, $this->aeId);
        $this->authorisationService->grantedAtOrganisation(PermissionAtOrganisation::AE_UPDATE_STATUS, $this->aeId);
        $this->authorisationService->grantedAtOrganisation(PermissionAtOrganisation::AE_UPDATE_TYPE, $this->aeId);
        $this->authorisationService->grantedAtOrganisation(PermissionAtOrganisation::AE_UPDATE_DVSA_AREA_OFFICE, $this->aeId);

        $this->oldPrimaryPhone1 = (new Phone())->setIsPrimary(true);
        $this->oldPrimaryPhone2 = (new Phone())->setIsPrimary(true);

        $this->oldPrimaryEmail2 = (new Email())->setIsPrimary(true);
        $this->oldPrimaryEmail1 = (new Email())->setIsPrimary(true);

        $this->authorisedExaminer = new Organisation();
        $this->authorisedExaminer->setName($this->aeName);

        $authForAe = new AuthorisationForAuthorisedExaminer();
        $authForAe->setNumber($this->aeNumber);
        $authForAe->setStatus((new AuthForAeStatus())
            ->setName('STATUS NAME')
        );
        $authForAe->setAreaOffice((new Site())
            ->setSiteNumber('1')
        );

        $this->authorisedExaminer->setAuthorisedExaminer($authForAe);
        $this->authorisedExaminer->setId($this->aeId);
        $this->authorisedExaminer->setCompanyType((new CompanyType())
            ->setName('COMPANY TYPE')
        );

        $this->organisationRepository = XMock::of(OrganisationRepository::class);
        $this->organisationRepository->expects($this->any())
            ->method('getAuthorisedExaminer')
            ->with($this->equalTo($this->aeId))
            ->willReturn($this->authorisedExaminer);

        $this->companyTypeRepository = $this->buildCompanyTypeRepositoryMock();

        $this->organisationRepositoryPersistSpy = new MethodSpy($this->organisationRepository, 'persist');

        $this->authForAeStatusRepository = XMock::of(AuthForAeStatusRepository::class);
        $this->authForAeStatusRepository->expects($this->any())
            ->method('getByCode')
            ->with($this->equalTo(AuthorisationForAuthorisedExaminerStatusCode::APPROVED))
            ->willReturn((new AuthForAeStatus())
                ->setCode(AuthorisationForAuthorisedExaminerStatusCode::APPROVED)
            );

        $identity = new IdentityStub($this->username);
        $identityProvider = new ApiIdentityProviderStub($identity);

        $this->siteRepository = XMock::of(SiteRepository::class);
        $this->siteRepository->expects($this->any())
            ->method('getAllAreaOffices')
            ->willReturn([
                $this->areaOfficeNumber => [
                    'id' => 1,
                    'areaOfficeNumber' => '01',
                ],
            ]);
        $this->siteRepository->expects($this->any())
            ->method('get')
            ->willReturn(
                (new Site())
                    ->setSiteNumber('01')
            );

        $this->updateAeService = new UpdateAeDetailsService(
            $this->organisationRepository,
            $this->organisationContactTypeRepository,
            $this->companyTypeRepository,
            $this->entityManager,
            $this->authorisationService,
            new PhoneContactTypeRepositoryFake(),
            $this->authForAeStatusRepository,
            $this->siteRepository,
            $eventService,
            $identityProvider
        );
    }

    /*
     * #########################################
     *
     * Testing all address fields
     *
     * #########################################
     */

    /**
     * @dataProvider dataProvider_address_validatesIfEvenSingleFieldExists
     *
     * @param $fieldName
     */
    public function test_address_validatesIfEvenSingleFieldExists($fieldName)
    {
        // GIVEN I have at least one field from registered address
        $data = [$fieldName => ''];

        // WHEN I update AE
        try {
            $this->updateAeService->update($this->aeId, $data);

            // THEN validation occurs
            $this->fail('Validation did not occur');
        } catch (ServiceException $ex) {
        }

        //AND nothing is saved
        $this->assertEquals(0, $this->entityManagerSpy->persistCount());
        $this->assertEquals(0, $this->eventServiceSpy->invocationCount());
    }

    public function dataProvider_address_validatesIfEvenSingleFieldExists()
    {
        return [
            [AuthorisedExaminerPatchModel::REGISTERED_ADDRESS_LINE_1],
            [AuthorisedExaminerPatchModel::REGISTERED_ADDRESS_LINE_2],
            [AuthorisedExaminerPatchModel::REGISTERED_ADDRESS_LINE_3],
            [AuthorisedExaminerPatchModel::REGISTERED_ADDRESS_TOWN],
            [AuthorisedExaminerPatchModel::REGISTERED_ADDRESS_COUNTRY],
            [AuthorisedExaminerPatchModel::REGISTERED_ADDRESS_POSTCODE],
            [AuthorisedExaminerPatchModel::CORRESPONDENCE_ADDRESS_LINE_1],
            [AuthorisedExaminerPatchModel::CORRESPONDENCE_ADDRESS_LINE_2],
            [AuthorisedExaminerPatchModel::CORRESPONDENCE_ADDRESS_LINE_3],
            [AuthorisedExaminerPatchModel::CORRESPONDENCE_ADDRESS_TOWN],
            [AuthorisedExaminerPatchModel::CORRESPONDENCE_ADDRESS_COUNTRY],
            [AuthorisedExaminerPatchModel::CORRESPONDENCE_ADDRESS_POSTCODE],
        ];
    }

    /**
     * @dataProvider dataProvider_address_permissionsAreChecked
     *
     * @param $propertyField
     * @param $requiredPermission
     */
    public function test_address_permissionsAreChecked($propertyField, $requiredPermission)
    {
        // GIVEN I'm changing a field
        $data = [$propertyField => ''];
        //AND I do not have a permission to do that
        $this->authorisationService->clearAll();

        // WHEN I update AE
        try {
            $this->updateAeService->update($this->aeId, $data);

            // THEN validation occurs
            $this->fail('Authorisation did not occur');
        } catch (UnauthorisedException $ex) {
        }

        //AND nothing is saved
        $this->assertEquals(0, $this->entityManagerSpy->persistCount());
        $this->assertEquals(0, $this->eventServiceSpy->invocationCount());
    }

    public function dataProvider_address_permissionsAreChecked()
    {
        return [
            [AuthorisedExaminerPatchModel::REGISTERED_ADDRESS_LINE_1, PermissionAtOrganisation::AE_UPDATE_REGISTERED_OFFICE_ADDRESS],
            [AuthorisedExaminerPatchModel::CORRESPONDENCE_ADDRESS_LINE_1, PermissionAtOrganisation::AE_UPDATE_CORRESPONDENCE_ADDRESS],
            [AuthorisedExaminerPatchModel::REGISTERED_PHONE, PermissionAtOrganisation::AE_UPDATE_REGISTERED_OFFICE_PHONE],
            [AuthorisedExaminerPatchModel::CORRESPONDENCE_PHONE, PermissionAtOrganisation::AE_UPDATE_REGISTERED_OFFICE_PHONE],
            [AuthorisedExaminerPatchModel::REGISTERED_EMAIL, PermissionAtOrganisation::AE_UPDATE_REGISTERED_OFFICE_EMAIL],
            [AuthorisedExaminerPatchModel::CORRESPONDENCE_EMAIL, PermissionAtOrganisation::AE_UPDATE_REGISTERED_OFFICE_EMAIL],
            [AuthorisedExaminerPatchModel::NAME, PermissionAtOrganisation::AE_UPDATE_NAME],
            [AuthorisedExaminerPatchModel::TRADING_NAME, PermissionAtOrganisation::AE_UPDATE_TRADING_NAME],
            [AuthorisedExaminerPatchModel::TYPE, PermissionAtOrganisation::AE_UPDATE_TYPE],
            [AuthorisedExaminerPatchModel::STATUS, PermissionAtOrganisation::AE_UPDATE_STATUS],
            [AuthorisedExaminerPatchModel::AREA_OFFICE, PermissionAtOrganisation::AE_UPDATE_DVSA_AREA_OFFICE],
        ];
    }

    /*
     * #########################################
     *
     * Testing address
     *
     * #########################################
     */

    /**
     * @dataProvider dataProvider_contactDetails
     *
     * @param $testArguments
     */
    public function test_address_persistsCorrectAddress(AuthorisedExaminerPatchModel $testArguments)
    {
        // GIVEN I have correct data for address

        $line1 = 'Line 1';
        $line2 = 'Line 2';
        $line3 = 'Line 3';
        $postcode = 'A postcode';
        $country = 'The country';
        $town = 'The town';

        $data = [
            $testArguments->getAddressLine1Field() => $line1,
            $testArguments->getAddressLine2Field() => $line2,
            $testArguments->getAddressLine3Field() => $line3,
            $testArguments->getPostcodeField() => $postcode,
            $testArguments->getCountryField() => $country,
            $testArguments->getTownField() => $town,
        ];

        // WHEN update the details

        $this->updateAeService->update($this->aeId, $data);

        // THEN address is saved

        /** @var Address[] $addresses */
        $contact = $this->authorisedExaminer->getContactByType($testArguments->getOrganisationContactTypeCode());
        $contactDetails = $contact->getDetails();
        $address = $contactDetails->getAddress();

        $this->assertPersisted($address);
        $this->assertPersisted($contactDetails);
        $this->assertPersisted($this->authorisedExaminer);

        $this->assertEquals($line1, $address->getAddressLine1());
        $this->assertEquals($line2, $address->getAddressLine2());
        $this->assertEquals($line3, $address->getAddressLine3());
        $this->assertEquals($town, $address->getTown());
        $this->assertEquals($postcode, $address->getPostcode());
        $this->assertEquals($country, $address->getCountry());
    }

    /**
     * @dataProvider dataProvider_contactDetails
     *
     * @param $testArguments
     */
    public function test_address_noMandatoryData(AuthorisedExaminerPatchModel $testArguments)
    {
        // GIVEN I provided address without first line
        $data = [
            $testArguments->getAddressLine1Field() => '',
            $testArguments->getTownField() => null,
            $testArguments->getPhoneField() => '',
        ];

        try {
            // WHEN I update AE
            $this->updateAeService->update($this->aeId, $data);

            $this->fail('Validation exception was expected');
        } catch (ServiceException $e) {
            // THEN I get an error saying that Address is required
            $this->assertContainsError($testArguments->getAddressLine1Field().' is required', $e);
            $this->assertContainsError($testArguments->getTownField().' is required', $e);
            $this->assertContainsError($testArguments->getPostcodeField().' is required', $e);
            $this->assertContainsError($testArguments->getPhoneField().' is required', $e);
        }

        // THEN nothing is persisted
        $this->assertEquals(0, $this->entityManagerSpy->persistCount());
        $this->assertEquals(0, $this->eventServiceSpy->invocationCount());
    }

    public function dataProvider_contactDetails()
    {
        return [
            [AuthorisedExaminerPatchModel::createForRegisteredContact()],
            [AuthorisedExaminerPatchModel::createForCorrespondenceContact()],
        ];
    }

    /**
     * @dataProvider dataProvider_contactDetails
     *
     * @param $testArguments
     */
    public function test_address_tooLongValidation(AuthorisedExaminerPatchModel $testArguments)
    {
        // GIVEN I provided contact details with too long inputs

        $tooLongAddressLine = str_repeat('A', AeAddressValidator::MAX_ADDRESS_LINE_LENGTH + 1);
        $tooLongTown = str_repeat('A', AeAddressValidator::MAX_TOWN_LENGTH + 1);
        $tooLongCountry = str_repeat('A', AeAddressValidator::MAX_COUNTRY_LENGTH + 1);
        $tooLongPostcode = str_repeat('A', AeAddressValidator::MAX_POSTCODE_LENGTH + 1);
        $tooLongEmail = str_repeat('A', AeEmailValidator::MAX_EMAIL_LENGTH + 1);
        $tooLongPhone = str_repeat('A', AePhoneValidator::MAX_PHONE_LENGTH + 1);

        $data = [
            $testArguments->getAddressLine1Field() => $tooLongAddressLine,
            $testArguments->getAddressLine2Field() => $tooLongAddressLine,
            $testArguments->getAddressLine3Field() => $tooLongAddressLine,
            $testArguments->getCountryField() => $tooLongCountry,
            $testArguments->getTownField() => $tooLongTown,
            $testArguments->getPostcodeField() => $tooLongPostcode,

            $testArguments->getEmailField() => $tooLongEmail,
            $testArguments->getPhoneField() => $tooLongPhone,
        ];

        try {
            // WHEN I update AE
            $this->updateAeService->update($this->aeId, $data);

            $this->fail('Exception was expected');
        } catch (BadRequestException $e) {
            // THEN I get an error saying that property is too long line was too long
            $this->assertContainsError($testArguments->getAddressLine1Field().' - must be '.AeAddressValidator::MAX_ADDRESS_LINE_LENGTH.' characters or less', $e);
            $this->assertContainsError($testArguments->getAddressLine2Field().' - must be '.AeAddressValidator::MAX_ADDRESS_LINE_LENGTH.' characters or less', $e);
            $this->assertContainsError($testArguments->getAddressLine3Field().' - must be '.AeAddressValidator::MAX_ADDRESS_LINE_LENGTH.' characters or less', $e);
            $this->assertContainsError($testArguments->getPhoneField().' - must be '.AePhoneValidator::MAX_PHONE_LENGTH.' characters or less', $e);
            $this->assertContainsError($testArguments->getEmailField().' - must be '.AeEmailValidator::MAX_EMAIL_LENGTH.' characters or less', $e);

            $this->assertContainsError($testArguments->getTownField().' - must be '.AeAddressValidator::MAX_TOWN_LENGTH.' characters or less', $e);
            $this->assertContainsError($testArguments->getCountryField().' - must be '.AeAddressValidator::MAX_COUNTRY_LENGTH.' characters or less', $e);
            $this->assertContainsError($testArguments->getPostcodeField().' - must be '.AeAddressValidator::MAX_POSTCODE_LENGTH.' characters or less', $e);
        }

        // AND nothing is saved
        $this->assertEquals(0, $this->entityManagerSpy->persistCount());
        $this->assertEquals(0, $this->eventServiceSpy->invocationCount());
    }

    /**
     * @dataProvider dataProvider_contactDetails
     *
     * @param $testArguments
     */
    public function test_email_validateIfInputIsCorrect(AuthorisedExaminerPatchModel $testArguments)
    {
        // GIVEN I provided data with incorrect email

        $data = [
            $testArguments->getAddressLine1Field() => 'address line 1',
            $testArguments->getTownField() => 'London',
            $testArguments->getPostcodeField() => 'ABC-DEF',

            $testArguments->getEmailField() => 'malformed_email',
        ];

        try {
            // WHEN I update AE
            $this->updateAeService->update($this->aeId, $data);

            $this->fail('Exception was expected');
        } catch (BadRequestException $e) {
            // THEN I get an messages saying email is incorrect
            $this->assertContainsError($testArguments->getEmailField().' - is invalid email', $e);
        }

        // AND nothing is saved
        $this->assertEquals(0, $this->entityManagerSpy->persistCount());
        $this->assertEquals(0, $this->eventServiceSpy->invocationCount());
    }

    /**
     * @dataProvider dataProvider_test_details_propertyIsPersisted()
     */
    public function test_details_propertyIsPersisted($data, $expectedSave, $expectedException = false, $expectedEvent = false)
    {
        if ($expectedException) {
            $this->setExpectedException($expectedException);
        }
        $this->updateAeService->update($this->aeId, $data);

        $saveCount = $this->organisationRepositoryPersistSpy->invocationCount();

        $this->assertEquals((bool) $expectedSave, $saveCount);

        if (!empty($expectedEvent)) {

            // AND event is saved
            $this->assertEquals(1, $this->eventServiceSpy->invocationCount());
            $this->assertEquals($this->authorisedExaminer, $this->eventServiceSpy->paramsForLastInvocation()[0]);
            $this->assertEquals(EventTypeCode::UPDATE_AE, $this->eventServiceSpy->paramsForLastInvocation()[1]);
            $this->assertEquals($expectedEvent, $this->eventServiceSpy->paramsForLastInvocation()[2]);
        }
    }

    public function dataProvider_test_details_propertyIsPersisted()
    {
        return [
            [
                'data' => [
                    AuthorisedExaminerPatchModel::NAME => 'New name',
                ],
                'expectedSave' => true,
                'expectedException' => false,
                'expectedEvent' => 'Name has been updated from "Smart industries" to "New name" for Authorised Examiner AE0105 New name by user johny001',
            ],
            [
                'data' => [
                    AuthorisedExaminerPatchModel::TRADING_NAME => 'New trading name',
                ],
                'expectedSave' => true,
            ],
            [
                'data' => [
                    AuthorisedExaminerPatchModel::STATUS => AuthorisationForAuthorisedExaminerStatusCode::APPROVED,
                ],
                'expectedSave' => true,
            ],
            //COMPANY TYPE
            [
                'data' => [
                    AuthorisedExaminerPatchModel::TYPE => CompanyTypeCode::LIMITED_LIABILITY_PARTNERSHIP,
                ],
                'expectedSave' => true,
            ],
            [
                'data' => [
                    AuthorisedExaminerPatchModel::TYPE => CompanyTypeCode::PARTNERSHIP,
                ],
                'expectedSave' => true,
            ],
            [
                'data' => [
                    AuthorisedExaminerPatchModel::TYPE => CompanyTypeCode::PUBLIC_BODY,
                ],
                'expectedSave' => true,
            ],
            [
                'data' => [
                    AuthorisedExaminerPatchModel::TYPE => CompanyTypeCode::SOLE_TRADER,
                ],
                'expectedSave' => true,
            ],
            [
                'data' => [
                    AuthorisedExaminerPatchModel::TYPE => CompanyTypeCode::COMPANY,
                    AuthorisedExaminerPatchModel::COMPANY_NUMBER => $this->companyNumber,
                ],
                'expectedSave' => true,
            ],
            //check company type without company number being set
            [
                'data' => [
                    AuthorisedExaminerPatchModel::TYPE => CompanyTypeCode::COMPANY,
                ],
                'expectedSave' => false,
                'expectedException' => BadRequestException::class,
            ],
            [
                'data' => [
                    AuthorisedExaminerPatchModel::AREA_OFFICE => 1,
                ],
                'expectedSave' => true,
            ],
            [
                'data' => [
                    AuthorisedExaminerPatchModel::TRADING_NAME => 'New trading name',
                ],
                'expectedSave' => true,
            ],
            [
                'data' => [
                    AuthorisedExaminerPatchModel::TRADING_NAME => '',
                ],
                'expectedSave' => false,
                'expectedException' => BadRequestException::class,
            ],
        ];
    }

    /*
     * #########################################
     *
     * Testing phone
     *
     * #########################################
     */

    /**
     * @dataProvider dataProvider_contactDetails
     *
     * @param $testArguments
     */
    public function test_phone_persistCorrectPhone(AuthorisedExaminerPatchModel $testArguments)
    {
        $contactType = $testArguments->getOrganisationContactTypeCode();

        // GIVEN I have a correct phone
        $newPhoneNumber = '123-456-759';
        $data = [$testArguments->getPhoneField() => $newPhoneNumber];

        // WHEN I update it
        $this->updateAeService->update($this->aeId, $data);

        // THEN the phone is persisted as primary
        $primaryPhone = $this->authorisedExaminer->getContactByType($contactType)->getDetails()->getPrimaryPhone();
        $this->assertNotNull($primaryPhone);
        $this->assertPersisted($primaryPhone);

        // WITH correct number
        $this->assertEquals($newPhoneNumber, $primaryPhone->getNumber());

        // AND contact is persisted
        $this->assertPersisted($this->authorisedExaminer->getContactByType($contactType)->getDetails());

        // AND AE is persisted
        $this->assertPersisted($this->authorisedExaminer);

        // AND phone is primary
        $this->assertTrue($primaryPhone->getIsPrimary());
    }

    /**
     * @dataProvider dataProvider_contactDetails
     *
     * @param $testArguments
     */
    public function test_phone_oldPrimaryPhonesAreDiscarded(AuthorisedExaminerPatchModel $testArguments)
    {
        /** @var OrganisationContactType $type */
        $type = $this->organisationContactTypeRepository->getByCode($testArguments->getOrganisationContactTypeCode());

        // GIVEN I have an ae with many primary phone numbers
        $this->authorisedExaminer->setContact(
            (new ContactDetail())
                ->addPhone($this->oldPrimaryPhone1)
                ->addPhone($this->oldPrimaryPhone2),
            $type
        );

        // AND I have a correct phone
        $newPhoneNumber = '123-456-759';
        $data = [$testArguments->getPhoneField() => $newPhoneNumber];

        // WHEN I update it

        $this->updateAeService->update($this->aeId, $data);

        $contact = $this->authorisedExaminer->getContactByType($testArguments->getOrganisationContactTypeCode())->getDetails();

        // THEN other primary phones are removed
        $this->assertRemoved($this->oldPrimaryPhone1);
        $this->assertNotContains($this->oldPrimaryPhone1, $contact->getPhones());
        $this->assertRemoved($this->oldPrimaryPhone2);
        $this->assertNotContains($this->oldPrimaryPhone2, $contact->getPhones());
    }

    /*
     * #########################################
     *
     * Testing email
     *
     * #########################################
     */

    /**
     * @dataProvider dataProvider_contactDetails
     *
     * @param $testArguments
     */
    public function test_email_persistCorrectEmail(AuthorisedExaminerPatchModel $testArguments)
    {
        $contactType = $testArguments->getOrganisationContactTypeCode();

        // GIVEN I have a correct email
        $newEmail = 'updateaedetailsservicetest@dvsa.test';
        $data = [$testArguments->getEmailField() => $newEmail];

        // WHEN I update it

        $this->updateAeService->update($this->aeId, $data);

        // THEN the email is persisted as primary
        $primaryEmail = $this->authorisedExaminer->getContactByType($contactType)->getDetails()->getPrimaryEmail();
        $this->assertNotNull($primaryEmail);
        $this->assertPersisted($primaryEmail);

        // WITH correct number
        $this->assertEquals($newEmail, $primaryEmail->getEmail());

        // AND contact is persisted
        $this->assertPersisted($this->authorisedExaminer->getContactByType($contactType)->getDetails());

        // AND AE is persisted
        $this->assertPersisted($this->authorisedExaminer);

        // AND email is primary
        $this->assertTrue($primaryEmail->getIsPrimary());
    }

    /**
     * @dataProvider dataProvider_contactDetails
     *
     * @param $testArguments
     */
    public function test_email_oldPrimaryEmailsAreDiscarded(AuthorisedExaminerPatchModel $testArguments)
    {
        $contactTypeCode = $testArguments->getOrganisationContactTypeCode();

        /** @var OrganisationContactType $type */
        $type = $this->organisationContactTypeRepository->getByCode($contactTypeCode);

        // GIVEN I have an ae with many primary emails
        $this->authorisedExaminer->setContact(
            (new ContactDetail())
                ->addEmail($this->oldPrimaryEmail1)
                ->addEmail($this->oldPrimaryEmail2),
            $type
        );

        // AND I have a correct email
        $newEmail = 'updateaedetailsservicetest@dvsa.test';
        $data = [$testArguments->getEmailField() => $newEmail];

        // WHEN I update it

        $this->updateAeService->update($this->aeId, $data);

        $contact = $this->authorisedExaminer->getContactByType($contactTypeCode)->getDetails();

        // THEN other primary email are removed
        $this->assertRemoved($this->oldPrimaryEmail1);
        $this->assertNotContains($this->oldPrimaryEmail1, $contact->getEmails());
        $this->assertRemoved($this->oldPrimaryEmail2);
        $this->assertNotContains($this->oldPrimaryEmail2, $contact->getEmails());
    }

    public function test_status_isStatusChangeDateSaved()
    {
        $data = [
            AuthorisedExaminerPatchModel::STATUS => AuthorisationForAuthorisedExaminerStatusCode::APPROVED,
        ];

        $ae = $this->organisationRepository->getAuthorisedExaminer($this->aeId);
        $ae->getAuthorisedExaminer()->setStatusChangedOn(new \DateTime('2000-01-01'));
        $oldAeStatusChangeDate = $ae->getAuthorisedExaminer()->getStatusChangedOn();

        $this->updateAeService->update($ae->getId(), $data);

        $newAeStatusChangeDate = $ae->getAuthorisedExaminer()->getStatusChangedOn();
        $this->assertEquals(true, $newAeStatusChangeDate > $oldAeStatusChangeDate);
    }

    private function assertPersisted($object)
    {
        $this->assertTrue($this->entityManagerSpy->wasPersisted($object));
    }

    private function assertRemoved($object)
    {
        $this->assertTrue($this->entityManagerSpy->wasRemoved($object));
    }

    private function assertContainsError($expectedError, ServiceException $e)
    {
        $errors = $this->extractFlatMessagesFromException($e);

        $this->assertContains($expectedError, $errors);
    }

    private function extractFlatMessagesFromException(ServiceException $exception)
    {
        return ArrayUtils::map($exception->getErrors(), function (array $error) {
            return $error['displayMessage'];
        });
    }

    private function buildCompanyTypeRepositoryMock()
    {
        $repositoryMock = Xmock::of(CompanyTypeRepository::class);
        $repositoryMock->expects($this->any())
            ->method('getByCode')
            ->willReturn(new CompanyType());

        return $repositoryMock;
    }
}
