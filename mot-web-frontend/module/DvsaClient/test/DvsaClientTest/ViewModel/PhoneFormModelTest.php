<?php

namespace DvsaClientTest\ViewModel;

use DvsaClient\ViewModel\PhoneFormModel;
use DvsaCommon\Dto\Contact\PhoneDto;
use Zend\Stdlib\Parameters;

class PhoneFormModelTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var PhoneFormModel
     */
    private $model;

    public function setUp()
    {
        $this->model = new PhoneFormModel();
    }

    public function tearDown()
    {
        unset($this->model);
    }

    /**
     * @dataProvider dataProviderTestGetSet
     */
    public function testGetSet($property, $value, $expect = null)
    {
        $method = ucfirst($property);

        //  logical block: set value and check set method
        $result = $this->model->{'set'.$method}($value);
        $this->assertInstanceOf(PhoneFormModel::class, $result);

        //  logical block: check get method
        $expect = ($expect === null ? $value : $expect);
        $method = (is_bool($expect) ? '' : 'get').$method;
        $this->assertEquals($expect, $this->model->{$method}());
    }

    public function dataProviderTestGetSet()
    {
        return [
            [
                'property' => 'number',
                'value' => 'test_Nr_1231234',
            ],
            ['type', 'test_Type'],
            ['isPrimary', false],
        ];
    }

    /**
     * @dataProvider dataProviderTestFromPost
     */
    public function testFromPost($postData, $expect)
    {
        $model = $this->model->fromPost(new Parameters($postData));

        //  logical block :: check
        $this->assertInstanceOf(PhoneFormModel::class, $model);
        $this->assertEquals($expect, $model);
    }

    public function dataProviderTestFromPost()
    {
        return [
            [
                'postData' => [
                    PhoneFormModel::FIELD_NUMBER => 'test_Nr_123456',
                ],
                'expect' => (new PhoneFormModel())
                    ->setNumber('test_Nr_123456'),
            ],
        ];
    }

    public function testFromDto()
    {
        $dto = self::getTestDto();

        $model = $this->model->fromDto($dto);

        //  logical block :: check
        $this->assertInstanceOf(PhoneFormModel::class, $model);
        $this->assertEquals($dto, $model->toDto());
    }

    public function testToDto()
    {
        $dto = self::getTestDto();

        //  fill model with data
        $this->model
            ->setNumber($dto->getNumber())
            ->setIsPrimary($dto->isPrimary())
            ->setType($dto->getContactType());

        $actual = $this->model->toDto();

        //  logical block :: check
        $this->assertInstanceOf(PhoneDto::class, $actual);
        $this->assertEquals($dto, $actual);
    }

    /**
     * @dataProvider dataProviderTestIsValid
     */
    public function testIsValid($postData, $expect)
    {
        $isValid = $this->model
            ->fromPost(new Parameters($postData))
            ->isValid();

        $this->assertEquals($expect['isValid'], $isValid);

        foreach ($expect['errors'] as $field => $error) {
            $this->assertEquals($error, $this->model->getError($field));
        }
    }

    public function dataProviderTestIsValid()
    {
        return [
            //  set supply email, validation is true, because email valid and same
            [
                'postData' => [
                    PhoneFormModel::FIELD_NUMBER => 'test_12345678',
                ],
                'expect' => [
                    'isValid' => true,
                    'errors' => [],
                ],
            ],
            //  set supply email, validation is FALSE, because email invalid and conf not same
            [
                'postData' => [
                    PhoneFormModel::FIELD_NUMBER => '',
                ],
                'expect' => [
                    'isValid' => false,
                    'errors' => [
                        PhoneFormModel::FIELD_NUMBER => PhoneFormModel::ERR_REQUIRE,
                    ],
                ],
            ],
        ];
    }

    private static function getTestDto()
    {
        return (new PhoneDto())
            ->setNumber('test_1234567')
            ->setIsPrimary(true);
    }
}
