<?php

namespace DvsaClientTest\ViewModel;

use DvsaClient\ViewModel\AddressFormModel;
use DvsaCommon\Dto\Contact\AddressDto;
use Zend\Stdlib\Parameters;

class AddressFormModelTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AddressFormModel
     */
    private $model;

    public function setUp()
    {
        $this->model = new AddressFormModel();
    }

    public function tearDown()
    {
        unset($this->model);
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
        $this->assertInstanceOf(AddressFormModel::class, $result);

        //  logical block: check get method
        $expect = ($expect === null ? $value : $expect);
        $method = (is_bool($expect) ? 'is' : 'get').$method;
        $this->assertEquals($expect, $this->model->{$method}());
    }

    public function dataProviderTestGetSet()
    {
        return [
            [
                'property' => 'addressLine1',
                'value' => 'test_addressLine1',
            ],
            ['addressLine2', 'test_addressLine2'],
            ['addressLine3', 'test_addressLine3'],
            ['town', 'test_town'],
            ['country', 'test_country'],
            ['postCode', 'test_postCode'],
        ];
    }

    public function testFromPost()
    {
        $testPostData = self::getTestPostData();

        $model = $this->model->fromPost(new Parameters($testPostData));

        //  logical block :: check
        $this->assertInstanceOf(AddressFormModel::class, $model);
        $this->assertEquals(self::getTestDto(), $model->toDto());
    }

    public function testFromDto()
    {
        $dto = self::getTestDto();

        $model = $this->model->fromDto($dto);

        //  logical block :: check
        $this->assertInstanceOf(AddressFormModel::class, $model);
        $this->assertEquals($dto, $model->toDto());
    }

    public function testToDto()
    {
        $dto = self::getTestDto();

        //  fill model with data
        $this->model
            ->setAddressLine1($dto->getAddressLine1())
            ->setAddressLine2($dto->getAddressLine2())
            ->setAddressLine3($dto->getAddressLine3())
            ->setTown($dto->getTown())
            ->setCountry($dto->getCountry())
            ->setPostCode($dto->getPostcode());

        $actual = $this->model->toDto();

        //  logical block :: check
        $this->assertInstanceOf(AddressDto::class, $actual);
        $this->assertEquals($dto, $actual);
    }

    /**
     * @dataProvider dataProviderTestIsValid
     */
    public function testIsValid($postData, $expectErrs)
    {
        $isValid = $this->model
            ->fromPost(new Parameters($postData))
            ->isValid();

        $this->assertFalse($isValid);

        foreach ($expectErrs as $field => $error) {
            $this->assertEquals($error, $this->model->getError($field));
        }
    }

    public function dataProviderTestIsValid()
    {
        return [
            [
                'postData' => [
                    AddressFormModel::FIELD_TOWN => 'test_Tows',
                ],
                'errors' => [
                    AddressFormModel::FIELD_LINE1 => AddressFormModel::ERR_ADDRESS_REQUIRE,
                    AddressFormModel::FIELD_LINE2 => '',
                    AddressFormModel::FIELD_LINE3 => '',
                    AddressFormModel::FIELD_POSTCODE => AddressFormModel::ERR_POSTCODE_REQUIRE,
                ],
            ],
            [
                'postData' => [
                    AddressFormModel::FIELD_LINE1 => 'test_addrLine',
                    AddressFormModel::FIELD_POSTCODE => 'test_PostCode',
                ],
                'errors' => [
                    AddressFormModel::FIELD_TOWN => AddressFormModel::ERR_TOWN_REQUIRE,
                ],
            ],
        ];
    }

    public function testIsEmpty()
    {
        $this->assertTrue((new AddressFormModel())->isEmpty());
    }

    private static function getTestPostData()
    {
        $fields = [
            AddressFormModel::FIELD_LINE1,
            AddressFormModel::FIELD_LINE2,
            AddressFormModel::FIELD_LINE3,
            AddressFormModel::FIELD_TOWN,
            AddressFormModel::FIELD_COUNTRY,
            AddressFormModel::FIELD_POSTCODE,
        ];

        return array_combine($fields, array_filter(self::getTestDto()->toArray()));
    }

    private static function getTestDto()
    {
        return (new AddressDto())
            ->setAddressLine1('test_addrLine1')
            ->setAddressLine2('test_addrLine2')
            ->setAddressLine3('test_addrLine3')
            ->setTown('test_town')
            ->setCountry('test_country')
            ->setPostcode('test_postCode');
    }
}
