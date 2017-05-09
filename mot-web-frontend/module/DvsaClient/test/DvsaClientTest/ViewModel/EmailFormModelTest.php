<?php

namespace DvsaClientTest\ViewModel;

use DvsaClient\ViewModel\EmailFormModel;
use DvsaCommon\Dto\Contact\EmailDto;
use DvsaCommon\Validator\EmailAddressValidator;
use Zend\Stdlib\Parameters;

class EmailFormModelTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var EmailFormModel
     */
    private $model;

    public function setUp()
    {
        $this->model = new EmailFormModel();
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
        $this->assertInstanceOf(EmailFormModel::class, $result);

        //  logical block: check get method
        $expect = ($expect === null ? $value : $expect);
        $method = (is_bool($expect) ? '' : 'get').$method;
        $this->assertEquals($expect, $this->model->{$method}());
    }

    public function dataProviderTestGetSet()
    {
        return [
            [
                'property' => 'email',
                'value' => 'test_email',
            ],
            ['emailConfirm', 'test_EmailConf'],
            ['isSupplied', true],
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
        $this->assertInstanceOf(EmailFormModel::class, $model);
        $this->assertEquals($expect, $model);
    }

    public function dataProviderTestFromPost()
    {
        return [
            [
                'postData' => [
                    EmailFormModel::FIELD_EMAIL => 'test_email',
                    EmailFormModel::FIELD_EMAIL_CONFIRM => 'test_emailConf',
                    EmailFormModel::FIELD_IS_NOT_SUPPLY => 1,
                ],
                'expect' => (new EmailFormModel())
                    ->setEmail('test_email')
                    ->setEmailConfirm('test_emailConf')
                    ->setIsSupplied(false),
            ],
            [
                'postData' => [
                    EmailFormModel::FIELD_IS_NOT_SUPPLY => 0,
                ],
                'expect' => (new EmailFormModel())
                    ->setIsSupplied(true),
            ],
        ];
    }

    public function testFromDto()
    {
        $dto = self::getTestDto();

        $model = $this->model->fromDto($dto);

        //  logical block :: check
        $this->assertInstanceOf(EmailFormModel::class, $model);
        $this->assertEquals($dto, $model->toDto());
    }

    public function testToDto()
    {
        $dto = self::getTestDto();

        //  fill model with data
        $this->model
            ->setEmail($dto->getEmail())
            ->setEmailConfirm($dto->getEmail())
            ->setIsPrimary($dto->isPrimary())
            ->setIsSupplied(true);

        $actual = $this->model->toDto();

        //  logical block :: check
        $this->assertInstanceOf(EmailDto::class, $actual);
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
            //  set do not supply email, validation is true, because nothing to validate
            [
                'postData' => [
                    EmailFormModel::FIELD_IS_NOT_SUPPLY => 1,
                ],
                'expect' => [
                    'isValid' => true,
                    'errors' => [],
                ],
            ],
            //  set supply email, validation is true, because email valid and same
            [
                'postData' => [
                    EmailFormModel::FIELD_EMAIL => 'emailformmodeltest@'.EmailAddressValidator::TEST_DOMAIN,
                    EmailFormModel::FIELD_EMAIL_CONFIRM => 'emailformmodeltest@'.EmailAddressValidator::TEST_DOMAIN,
                    EmailFormModel::FIELD_IS_NOT_SUPPLY => 0,
                ],
                'expect' => [
                    'isValid' => true,
                    'errors' => [],
                ],
            ],
            //  set supply email, validation is FALSE, because email invalid and conf not same
            [
                'postData' => [
                    EmailFormModel::FIELD_EMAIL => 'test_Tows',
                    EmailFormModel::FIELD_EMAIL_CONFIRM => 'test_EmailConfirm',
                    EmailFormModel::FIELD_IS_NOT_SUPPLY => 0,
                ],
                'expect' => [
                    'isValid' => false,
                    'errors' => [
                        EmailFormModel::FIELD_EMAIL => EmailFormModel::ERR_INVALID,
                        EmailFormModel::FIELD_EMAIL_CONFIRM => EmailFormModel::ERR_CONF_NOT_SAME,
                    ],
                ],
            ],
        ];
    }

    private static function getTestDto()
    {
        return (new EmailDto())
            ->setEmail('emailformmodeltest@'.EmailAddressValidator::TEST_DOMAIN)
            ->setIsSupplied(true)
            ->setEmailConfirm('emailformmodeltest@'.EmailAddressValidator::TEST_DOMAIN)
            ->setIsPrimary(true);
    }
}
