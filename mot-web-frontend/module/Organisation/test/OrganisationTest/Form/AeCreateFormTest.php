<?php

namespace OrganisationTest\Form;

use DvsaClient\ViewModel\AddressFormModel;
use DvsaClient\ViewModel\ContactDetailFormModel;
use DvsaClient\ViewModel\EmailFormModel;
use DvsaClient\ViewModel\PhoneFormModel;
use DvsaCommon\Dto\Contact\AddressDto;
use DvsaCommon\Dto\Contact\EmailDto;
use DvsaCommon\Dto\Contact\PhoneDto;
use DvsaCommon\Dto\Organisation\AuthorisedExaminerAuthorisationDto;
use DvsaCommon\Dto\Organisation\OrganisationContactDto;
use DvsaCommon\Dto\Organisation\OrganisationDto;
use DvsaCommon\Enum\OrganisationContactTypeCode;
use DvsaCommon\Enum\PhoneContactTypeCode;
use DvsaCommonTest\TestUtils\TestCaseTrait;
use DvsaCommonTest\TestUtils\XMock;
use Organisation\Form\AeCreateForm;
use Zend\Stdlib\Parameters;

/**
 * I'm building my professional career on comments.
 */
class AeCreateFormTest extends \PHPUnit_Framework_TestCase
{
    use TestCaseTrait;

    /**
     * @var AeCreateForm
     */
    private $form;

    public function setUp()
    {
        $this->form = new AeCreateForm();
    }

    public function tearDown()
    {
        unset($this->form);
    }

    /**
     * @param string $property
     * @param mixed  $value
     * @param mixed  $expect
     *
     * @dataProvider dataProviderTestGetSet
     */
    public function testGetSet($property, $value, $expect = null)
    {
        $method = ucfirst($property);

        //  logical block: set value and check set method
        $result = $this->form->{'set'.$method}($value);
        $this->assertInstanceOf(AeCreateForm::class, $result);

        //  logical block: check get method
        $expect = ($expect === null ? $value : $expect);
        $method = (is_bool($expect) ? 'is' : 'get').$method;
        $this->assertEquals($expect, $this->form->{$method}());
    }

    public function dataProviderTestGetSet()
    {
        return [
            ['areaOfficeOptions', ['test_AO1', 'test_AO2']],
            ['companyTypes', ['test_CompanyType1', 'test_CompanyType1']],
            ['formUrl', ['test_FormUrl1', 'test_FormUrl2']],
        ];
    }

    /**
     * @dataProvider dataProviderTestFromPost
     */
    public function testFromPost($postData, $expect)
    {
        $postData = new Parameters($postData);

        //  logical block :: mocking
        $mockBusContactModel = XMock::of(ContactDetailFormModel::class, ['fromPost']);
        $this->mockMethod($mockBusContactModel, 'fromPost', $this->once(), null, [$postData]);

        $mockCorrContactModel = XMock::of(ContactDetailFormModel::class);
        $this->mockMethod($mockCorrContactModel, 'fromPost', $this->once(), null, [$postData]);

        //  mock objects in form object
        XMock::mockClassField($this->form, 'regContactModel', $mockBusContactModel);
        XMock::mockClassField($this->form, 'corrContactModel', $mockCorrContactModel);

        //  call
        $model = $this->form->fromPost($postData);

        //  logical block :: check
        //  check type of instances
        $this->assertInstanceOf(AeCreateForm::class, $model);

        //  check main fields
        $this->assertEquals($postData->get(AeCreateForm::FIELD_NAME), $model->getName());
        $this->assertEquals($postData->get(AeCreateForm::FIELD_TRADING_AS), $model->getTradingAs());
        $this->assertEquals($postData->get(AeCreateForm::FIELD_COMPANY_TYPE), $model->getCompanyType());
        $this->assertEquals($postData->get(AeCreateForm::FIELD_REG_NR), $model->getRegisteredCompanyNumber());
        $this->assertEquals($postData->get(AeCreateForm::FIELD_AO_NR), $model->getAssignedAreaOffice());

        $this->assertEquals($expect['isCorrTheSame'], $model->isCorrDetailsTheSame());
    }

    public function dataProviderTestFromPost()
    {
        return [
            [
                'postData' => [
                    AeCreateForm::FIELD_NAME => 'test_OrgName',
                    AeCreateForm::FIELD_TRADING_AS => 'test_TradingAs',
                    AeCreateForm::FIELD_COMPANY_TYPE => 'test_CompanyType',
                    AeCreateForm::FIELD_REG_NR => 'test_RegNr',
                    AeCreateForm::FIELD_AO_NR => 'test_AONr',
                    AeCreateForm::FIELD_IS_CORR_DETAILS_THE_SAME => 1,
                ],
                'expect' => [
                    'isCorrTheSame' => true,
                ],
            ],
            [
                'postData' => [
                    AeCreateForm::FIELD_IS_CORR_DETAILS_THE_SAME => null,
                ],
                'expect' => [
                    'isCorrTheSame' => false,
                ],
            ],
        ];
    }

    public function testFormSetsDefaultsWhenGivenNullDto()
    {
        $model = $this->form->fromDto(null);
        $this->assertTrue($model->isCorrDetailsTheSame());
    }

    public function testFormPopulatesFromDtoAsExpected()
    {
        $authForAeDto = new AuthorisedExaminerAuthorisationDto();
        $authForAeDto->setAssignedAreaOffice(1);

        $orgDto = (new OrganisationDto())
            ->setName('test_OrgName')
            ->setTradingAs('test_TradingAs')
            ->setCompanyType('test_CompanyType')
            ->setRegisteredCompanyNumber('test_RegNr')
            ->setAuthorisedExaminerAuthorisation($authForAeDto);

        $model = $this->form->fromDto($orgDto);

        $this->assertEquals($orgDto->getName(), $model->getName());
        $this->assertEquals($orgDto->getTradingAs(), $model->getTradingAs());
        $this->assertEquals($orgDto->getCompanyType(), $model->getCompanyType());
        $this->assertEquals($orgDto->getRegisteredCompanyNumber(), $model->getRegisteredCompanyNumber());
        $this->assertEquals($orgDto->getAuthorisedExaminerAuthorisation()->getAssignedAreaOffice(), $model->getAssignedAreaOffice());
    }

    /**
     * @dataProvider dataProviderTestFromDtoIsTheSame
     */
    public function testFromDtoIsTheSame(
        OrganisationContactDto $busContact = null,
        OrganisationContactDto $corrContact = null,
        $expectIsSame = null
    ) {
        $contacts = array_filter([$busContact, $corrContact]);
        if (!empty($contacts)) {
            $dto = new OrganisationDto();
            $dto->setContacts($contacts);
        } else {
            $dto = null;
        }

        $model = $this->form->fromDto($dto);

        //  --  logical block :: check
        $this->assertEquals($expectIsSame, $model->isCorrDetailsTheSame());

        //  check correspondence details model
        $corrContact = ($expectIsSame === true ? $busContact : $corrContact);
        $expectCorrModel = (new ContactDetailFormModel(OrganisationContactTypeCode::CORRESPONDENCE))
            ->fromDto($corrContact);
        $this->assertEquals($expectCorrModel, $model->getCorrContactModel());

        //  check business details model
        $expectBusModel = (new ContactDetailFormModel(OrganisationContactTypeCode::REGISTERED_COMPANY))
            ->fromDto($busContact);
        $this->assertEquals($expectBusModel, $model->getBusContactModel());
    }

    public function dataProviderTestFromDtoIsTheSame()
    {
        $busContactDto = $this->createContactDetails(
            OrganisationContactTypeCode::REGISTERED_COMPANY, 'test_addrLine1', 'test_email', 'test_phoneNr'
        );

        $corrContactDto = clone $busContactDto;
        $corrContactDto->setType(OrganisationContactTypeCode::CORRESPONDENCE);

        return [
            [
                'busContact' => null,
                'corrContact' => null,
                'expectIsSame' => true,
            ],
            //  check when corr details is null
            [
                'busContact' => $busContactDto,
                'corrContact' => null,
                'expectIsSame' => true,
            ],
            //  check when corr and bus contacts the same
            [
                'busContact' => $busContactDto,
                'corrContact' => $corrContactDto,
                'expectIsSame' => true,
            ],
            //  check when corr details not null, but address not same
            [
                'busContact' => $busContactDto,
                'corrContact' => $this->cloneObj($corrContactDto)->setAddress(new AddressDto()),
                'expectIsSame' => false,
            ],
            //  check when corr details not null, but phones not same
            [
                'busContact' => $busContactDto,
                'corrContact' => $this->cloneObj($corrContactDto)->setPhones([]),
                'expectIsSame' => false,
            ],
            //  check when corr details not null, but email not same
            [
                'busContact' => $busContactDto,
                'corrContact' => $this->cloneObj($corrContactDto)->setEmails([(new EmailDto())->setIsPrimary(true)]),
                'expectIsSame' => false,
            ],
        ];
    }

    /**
     * @dataProvider dataProviderTestToDto
     */
    public function testToDto($postData, $expect)
    {
        $actual = $this->form
            ->fromPost(new Parameters($postData))
            ->toDto();

        $this->assertEquals($expect, $actual);
    }

    public function dataProviderTestToDto()
    {
        $busContactDto = $this->createContactDetails(
            OrganisationContactTypeCode::REGISTERED_COMPANY, 'test_Addr1', 'test_Email1', 'test_Phone1'
        );

        $corrContactDto = clone $busContactDto;
        $corrContactDto->setType(OrganisationContactTypeCode::CORRESPONDENCE);

        $authForAeDto = new AuthorisedExaminerAuthorisationDto();
        $authForAeDto->setAssignedAreaOffice(1);

        return [
            [
                'postData' => [
                    AeCreateForm::FIELD_NAME => 'test_OrgName',
                    AeCreateForm::FIELD_TRADING_AS => 'test_TradingAs',
                    AeCreateForm::FIELD_COMPANY_TYPE => 'test_CompanyType',
                    AeCreateForm::FIELD_REG_NR => 'test_RegNr',
                    AeCreateForm::FIELD_AO_NR => '1',
                    AeCreateForm::FIELD_IS_CORR_DETAILS_THE_SAME => 1,
                    OrganisationContactTypeCode::REGISTERED_COMPANY => [
                        AddressFormModel::FIELD_LINE1 => 'test_Addr1',
                        PhoneFormModel::FIELD_NUMBER => 'test_Phone1',
                        EmailFormModel::FIELD_EMAIL => 'test_Email1',
                    ],
                ],
                'expect' => (new OrganisationDto())
                    ->setName('test_OrgName')
                    ->setTradingAs('test_TradingAs')
                    ->setCompanyType('test_CompanyType')
                    ->setRegisteredCompanyNumber('test_RegNr')
                    ->setAuthorisedExaminerAuthorisation($authForAeDto)
                    ->setContacts([$busContactDto, $corrContactDto]),
            ],
            [
                'postData' => [
                    AeCreateForm::FIELD_NAME => 'test_OrgName',
                    AeCreateForm::FIELD_IS_CORR_DETAILS_THE_SAME => 0,
                    AeCreateForm::FIELD_AO_NR => 1,
                    OrganisationContactTypeCode::CORRESPONDENCE => [
                        EmailFormModel::FIELD_EMAIL => 'test_Email',
                    ],
                ],
                'expect' => (new OrganisationDto())
                    ->setName('test_OrgName')
                    ->setAuthorisedExaminerAuthorisation($authForAeDto)
                    ->setContacts(
                        [
                            $this->createContactDetails(OrganisationContactTypeCode::REGISTERED_COMPANY),
                            $this->createContactDetails(
                                OrganisationContactTypeCode::CORRESPONDENCE, null, 'test_Email'
                            ),
                        ]
                    ),
            ],
        ];
    }

    /**
     * @return OrganisationDto|OrganisationContactDto
     */
    private function cloneObj($obj)
    {
        return clone $obj;
    }

    private function createContactDetails($contactType, $address = null, $email = null, $phone = null)
    {
        $addressDto = (new AddressDto());
        if ($address !== null) {
            $addressDto->setAddressLine1($address);
        }

        $phoneDto = (new PhoneDto())
            ->setContactType(PhoneContactTypeCode::BUSINESS)
            ->setIsPrimary(true);
        if ($phone !== null) {
            $phoneDto->setNumber($phone);
        }

        $emailDto = (new EmailDto())
            ->setIsSupplied(true)
            ->setIsPrimary(true);
        if ($email !== null) {
            $emailDto->setEmail($email);
        }

        $contactDto = (new OrganisationContactDto())
            ->setType($contactType)
            ->setAddress($addressDto)
            ->setPhones([$phoneDto])
            ->setEmails([$emailDto]);

        return $contactDto;
    }

    public function testAddErrorsFromApi()
    {
        $errors = [
            'field' => 'field',
            'displayMessage' => 'message',
        ];

        $this->form->addErrorsFromApi([$errors]);
        $this->assertEquals('message', $this->form->getError('field'));
    }
}
