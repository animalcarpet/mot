<?php

namespace SiteTest\Form;

use DvsaClient\ViewModel\AddressFormModel;
use DvsaClient\ViewModel\ContactDetailFormModel;
use DvsaClient\ViewModel\EmailFormModel;
use DvsaClient\ViewModel\PhoneFormModel;
use DvsaCommon\Constants\FacilityTypeCode;
use DvsaCommon\Dto\Contact\AddressDto;
use DvsaCommon\Dto\Contact\EmailDto;
use DvsaCommon\Dto\Contact\PhoneDto;
use DvsaCommon\Dto\Site\FacilityDto;
use DvsaCommon\Dto\Site\FacilityTypeDto;
use DvsaCommon\Dto\Site\SiteContactDto;
use DvsaCommon\Dto\Site\VehicleTestingStationDto;
use DvsaCommon\Enum\PhoneContactTypeCode;
use DvsaCommon\Enum\SiteContactTypeCode;
use DvsaCommon\Enum\SiteTypeCode;
use DvsaCommon\Enum\SiteTypeName;
use DvsaCommonTest\TestUtils\TestCaseTrait;
use DvsaCommonTest\TestUtils\XMock;
use Site\Form\VtsCreateForm;
use Zend\Stdlib\Parameters;

class VtsCreateFormTest extends \PHPUnit_Framework_TestCase
{
    use TestCaseTrait;

    const VTS_ID = 1;

    /** @var VtsCreateForm */
    private $model;

    public function setUp()
    {
        $this->model = new VtsCreateForm();
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
        $result = $this->model->{'set'.$method}($value);
        $this->assertInstanceOf(VtsCreateForm::class, $result);

        //  logical block: check get method
        $expect = ($expect === null ? $value : $expect);
        $method = (is_bool($expect) ? 'is' : 'get').$method;
        $this->assertEquals($expect, $this->model->{$method}());
    }

    public function dataProviderTestGetSet()
    {
        return [
            ['name', 'test_Name'],
            ['type', 'test_Type'],
            ['testingFacilityOptl', 'test_TestingFacilityOptl'],
            ['testingFacilityTptl', 'test_TestingFacilityTptl'],
            ['country', 'test_Country'],
            ['classes', 'test_Classes'],
            ['formUrl', 'test_FormUrl'],
        ];
    }

    /**
     * @dataProvider dataProviderTestFromPost
     */
    public function testFromPost($postData)
    {
        $postData = new Parameters($postData);

        //  logical block :: mocking
        $mockContactModel = XMock::of(ContactDetailFormModel::class, ['fromPost']);
        $this->mockMethod($mockContactModel, 'fromPost', $this->once(), null, [$postData]);

        //  mock objects in form object
        XMock::mockClassField($this->model, 'contactModel', $mockContactModel);

        //  call
        $model = $this->model->fromPost($postData);

        //  logical block :: check
        //  check type of instances
        $this->assertInstanceOf(VtsCreateForm::class, $model);

        //  check main fields
        $this->assertEquals($postData->get(VtsCreateForm::FIELD_NAME), $model->getName());
    }

    public function dataProviderTestFromPost()
    {
        return [
            [
                'postData' => [
                    VtsCreateForm::FIELD_SITE_TYPE => SiteTypeCode::VEHICLE_TESTING_STATION,
                    VtsCreateForm::FIELD_NAME => 'test_SiteName',
                    VtsCreateForm::FIELD_COUNTRY => 'country',
                    VtsCreateForm::FIELD_TESTING_FACILITY_OPTL => 1,
                    VtsCreateForm::FIELD_TESTING_FACILITY_TPTL => 1,
                    VtsCreateForm::FIELD_VEHICLE_CLASS => [1, 2],
                    SiteContactTypeCode::BUSINESS => [
                        AddressFormModel::FIELD_LINE1 => 'test_Addr1',
                        PhoneFormModel::FIELD_NUMBER => 'test_Phone1',
                        EmailFormModel::FIELD_EMAIL => 'test_Email1',
                    ],
                ],
            ],
        ];
    }

    /**
     * @dataProvider dataProviderTestToDto
     */
    public function testToDto($postData, $expect)
    {
        $actual = $this->model
            ->fromPost(new Parameters($postData))
            ->toDto();

        $this->assertEquals($expect, $actual);
    }

    public function dataProviderTestToDto()
    {
        $contactDto = $this->createContactDetails(
            SiteContactTypeCode::BUSINESS, 'test_Addr1', 'test_Email1', 'test_Phone1'
        );

        $facilities = [
            (new FacilityDto())->setType((new FacilityTypeDto())->setCode(FacilityTypeCode::ONE_PERSON_TEST_LANE)),
            (new FacilityDto())->setType((new FacilityTypeDto())->setCode(FacilityTypeCode::TWO_PERSON_TEST_LANE)),
            (new FacilityDto())->setType((new FacilityTypeDto())->setCode(FacilityTypeCode::TWO_PERSON_TEST_LANE)),
        ];

        return [
            [
                'postData' => [
                    VtsCreateForm::FIELD_SITE_TYPE => SiteTypeCode::VEHICLE_TESTING_STATION,
                    VtsCreateForm::FIELD_NAME => 'test_SiteName',
                    VtsCreateForm::FIELD_TESTING_FACILITY_OPTL => 1,
                    VtsCreateForm::FIELD_TESTING_FACILITY_TPTL => 2,
                    VtsCreateForm::FIELD_VEHICLE_CLASS => [1, 2],
                    VtsCreateForm::FIELD_COUNTRY => 'country',
                    SiteContactTypeCode::BUSINESS => [
                        AddressFormModel::FIELD_LINE1 => 'test_Addr1',
                        PhoneFormModel::FIELD_NUMBER => 'test_Phone1',
                        EmailFormModel::FIELD_EMAIL => 'test_Email1',
                    ],
                ],
                'expect' => (new VehicleTestingStationDto())
                    ->setIsOptlSelected(true)
                    ->setIsTptlSelected(true)
                    ->setName('test_SiteName')
                    ->setContacts([$contactDto])
                    ->setFacilities($facilities)
                    ->setType(SiteTypeCode::VEHICLE_TESTING_STATION)
                    ->setTestClasses([1, 2]),
            ],
        ];
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

        $contactDto = (new SiteContactDto())
            ->setType($contactType)
            ->setAddress($addressDto)
            ->setPhones([$phoneDto])
            ->setEmails([$emailDto]);

        return $contactDto;
    }

    public function testGetDropDown()
    {
        $this->assertSame(
            [0, 1, 2, 3, 4, '5 or more'],
            $this->model->getTestingFacilities()
        );
        $this->assertSame(
            [
                SiteTypeCode::VEHICLE_TESTING_STATION => SiteTypeName::VEHICLE_TESTING_STATION,
                SiteTypeCode::AREA_OFFICE => SiteTypeName::AREA_OFFICE,
                SiteTypeCode::TRAINING_CENTRE => SiteTypeName::TRAINING_CENTRE,
            ],
            $this->model->getSiteTypes()
        );
        $this->assertInstanceOf(ContactDetailFormModel::class, $this->model->getContactModel());
    }
}
