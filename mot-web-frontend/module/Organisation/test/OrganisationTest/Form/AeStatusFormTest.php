<?php

namespace OrganisationTest\Form;

use DvsaCommon\Dto\Common\AuthForAeStatusDto;
use DvsaCommon\Dto\Organisation\AuthorisedExaminerAuthorisationDto;
use DvsaCommon\Dto\Organisation\OrganisationDto;
use DvsaCommon\Enum\AuthorisationForAuthorisedExaminerStatusCode;
use Organisation\Form\AeStatusForm;
use Zend\Stdlib\Parameters;

class AeStatusFormTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AeStatusForm
     */
    private $form;

    public function setUp()
    {
        $this->form = new AeStatusForm();
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
        $this->assertInstanceOf(AeStatusForm::class, $result);

        //  logical block: check get method
        $expect = ($expect === null ? $value : $expect);
        $method = (is_bool($expect) ? 'is' : 'get').$method;
        $this->assertEquals($expect, $this->form->{$method}());
    }

    public function dataProviderTestGetSet()
    {
        return [
            ['status', ['test_status1', 'test_status2']],
            ['formUrl', ['test_Url1', 'test_Url2']],
        ];
    }

    public function testFromPost()
    {
        $postData = new Parameters(
            [AeStatusForm::FIELD_STATUS => AuthorisationForAuthorisedExaminerStatusCode::APPLIED]
        );

        //  call
        $model = $this->form->fromPost($postData);

        //  logical block :: check
        //  check type of instances
        $this->assertInstanceOf(AeStatusForm::class, $model);

        //  check main fields
        $this->assertEquals($postData->get(AeStatusForm::FIELD_STATUS), $model->getStatus());
    }

    public function testToDto()
    {
        $actual = $this->form
            ->fromPost(
                new Parameters(
                    [AeStatusForm::FIELD_STATUS => AuthorisationForAuthorisedExaminerStatusCode::APPLIED]
                )
            )
            ->toDto();

        $this->assertEquals($this->getOrganisation(), $actual);
    }

    private function getOrganisation()
    {
        $status = (new AuthForAeStatusDto())
            ->setCode(AuthorisationForAuthorisedExaminerStatusCode::APPLIED);

        $auth = (new AuthorisedExaminerAuthorisationDto())
            ->setStatus($status);

        $dto = (new OrganisationDto())
            ->setAuthorisedExaminerAuthorisation($auth);

        return $dto;
    }

    public function testGetStatuses()
    {
        $this->assertArrayHasKey(AuthorisationForAuthorisedExaminerStatusCode::APPROVED, $this->form->getStatuses());
        $this->assertCount(7, $this->form->getStatuses());
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
