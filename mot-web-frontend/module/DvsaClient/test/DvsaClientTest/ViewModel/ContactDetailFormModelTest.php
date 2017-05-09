<?php

namespace DvsaClientTest\ViewModel;

use DvsaClient\ViewModel\AddressFormModel;
use DvsaClient\ViewModel\ContactDetailFormModel;
use DvsaClient\ViewModel\EmailFormModel;
use DvsaClient\ViewModel\PhoneFormModel;
use DvsaCommon\Dto\Contact\ContactDto;
use DvsaCommon\Dto\Organisation\OrganisationContactDto;
use DvsaCommon\Enum\OrganisationContactTypeCode;
use DvsaCommonTest\TestUtils\TestCaseTrait;
use DvsaCommonTest\TestUtils\XMock;
use Zend\Stdlib\Parameters;

class ContactDetailFormModelTest extends \PHPUnit_Framework_TestCase
{
    use TestCaseTrait;

    const TYPE = OrganisationContactTypeCode::REGISTERED_COMPANY;

    /**
     * @var ContactDetailFormModel
     */
    private $model;
    /**
     * @var AddressFormModel
     */
    private $mockAddressModel;
    /**
     * @var EmailFormModel
     */
    private $mockEmailModel;
    /**
     * @var PhoneFormModel
     */
    private $mockPhoneModel;

    public function setUp()
    {
        $this->model = $this->getMockBuilder(ContactDetailFormModel::class)
            ->setConstructorArgs([self::TYPE])
            ->setMethods(['getAddressModel', 'getEmailModel', 'getPhoneModel'])
            ->getMock();

        $this->mockAddressModel = XMock::of(AddressFormModel::class, ['fromPost', 'fromDto', 'toDto', 'isValid']);
        $this->mockMethod($this->model, 'getAddressModel', $this->any(), $this->mockAddressModel);

        $this->mockEmailModel = XMock::of(EmailFormModel::class, ['fromPost', 'fromDto', 'toDto', 'isValid']);
        $this->mockMethod($this->model, 'getEmailModel', $this->any(), $this->mockEmailModel);

        $this->mockPhoneModel = XMock::of(PhoneFormModel::class, ['fromPost', 'fromDto', 'toDto', 'isValid']);
        $this->mockMethod($this->model, 'getPhoneModel', $this->any(), $this->mockPhoneModel);
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
        $this->model = new ContactDetailFormModel('type');

        $method = ucfirst($property);

        //  logical block: set value and check set method
        $result = $this->model->{'set'.$method}($value);
        $this->assertInstanceOf(ContactDetailFormModel::class, $result);

        //  logical block: check get method
        $expect = ($expect === null ? $value : $expect);
        $method = (is_bool($expect) ? '' : 'get').$method;
        $this->assertEquals($expect, $this->model->{$method}());
    }

    public function dataProviderTestGetSet()
    {
        return [
            [
                'property' => 'emailModel',
                'value' => new  EmailFormModel(),
            ],
            ['addressModel', new AddressFormModel()],
            ['phoneModel', new PhoneFormModel()],
        ];
    }

    /**
     * @dataProvider dataProviderTestFromPost
     */
    public function testFromPost(array $postData, $isExpectCall)
    {
        $postData = new Parameters($postData);

        //  logical block :: mocking
        $data = new Parameters($postData->get(self::TYPE));

        $this->mockMethod($this->mockEmailModel, 'fromPost', $this->isCall($isExpectCall), null, [$data]);
        $this->mockMethod($this->mockAddressModel, 'fromPost', $this->isCall($isExpectCall), null, [$data]);
        $this->mockMethod($this->mockPhoneModel, 'fromPost', $this->isCall($isExpectCall), null, [$data]);

        $actual = $this->model->fromPost($postData);

        $this->assertInstanceOf(ContactDetailFormModel::class, $actual);
    }

    public function dataProviderTestFromPost()
    {
        return [
            [
                'postData' => [
                    self::TYPE => [
                        'someKey' => 'someData',
                    ],
                ],
                'isExpectCall' => true,
            ],
            [
                'postData' => [
                    self::TYPE => [],
                ],
                'isExpectCall' => false,
            ],
        ];
    }

    /**
     * @dataProvider dataProviderTestFromDto
     */
    public function testFromDto($dto, $isExpectCall)
    {
        //  logical block :: mocking
        $this->mockMethod($this->mockEmailModel, 'fromDto', $this->isCall($isExpectCall));
        $this->mockMethod($this->mockAddressModel, 'fromDto', $this->isCall($isExpectCall));
        $this->mockMethod($this->mockPhoneModel, 'fromDto', $this->isCall($isExpectCall));

        $actual = $this->model->fromDto($dto);

        $this->assertInstanceOf(ContactDetailFormModel::class, $actual);
    }

    public function dataProviderTestFromDto()
    {
        return [
            [
                'dto' => new ContactDto(),
                'isExpectCall' => true,
            ],
            [
                'dto' => null,
                'isExpectCall' => false,
            ],
        ];
    }

    /**
     * @dataProvider dataProviderTestToDto
     */
    public function testToDto($passDto, $expectInstanceOf)
    {
        //  logical block :: mocking
        $this->mockMethod($this->mockEmailModel, 'toDto', $this->once());
        $this->mockMethod($this->mockAddressModel, 'toDto', $this->once());
        $this->mockMethod($this->mockPhoneModel, 'toDto', $this->once());

        $actual = $this->model->toDto($passDto);

        $this->assertInstanceOf($expectInstanceOf, $actual);
    }

    public function dataProviderTestToDto()
    {
        return [
            [
                'passDto' => null,
                'expectInstanceOf' => ContactDto::class,
            ],
            [
                'passDto' => new OrganisationContactDto(),
                'expectInstanceOf' => OrganisationContactDto::class,
            ],
        ];
    }

    private function isCall($isExpectCall)
    {
        return $isExpectCall ? $this->once() : $this->never();
    }
}
