<?php

namespace OrganisationTest\UpdateAeProperty\Form;

use Application\Service\CatalogService;
use Core\Catalog\Authorisation\AuthForAuthorisedExaminerStatusCatalog;
use DvsaCommon\Enum\AuthorisationForAuthorisedExaminerStatusCode;
use DvsaCommonTest\TestUtils\XMock;
use Organisation\UpdateAeProperty\Process\Form\StatusPropertyForm;
use PHPUnit_Framework_MockObject_MockObject as MockObj;

class StatusPropertyFormTest extends \PHPUnit_Framework_TestCase
{
    /*
     * @var AuthForAuthorisedExaminerStatusCatalog|MockObj
     */
    protected $catalog;

    public function setUp()
    {
        $this->catalog = XMock::of(CatalogService::class, []);

        $this->catalog->expects($this->any())
            ->method('getAuthForAuthorisedExaminerStatuses')
            ->willReturn([
                AuthorisationForAuthorisedExaminerStatusCode::APPLIED => 'Applied',
                AuthorisationForAuthorisedExaminerStatusCode::APPROVED => 'Approved',
                AuthorisationForAuthorisedExaminerStatusCode::REJECTED => 'Rejected',
                AuthorisationForAuthorisedExaminerStatusCode::RETRACTED => 'Retracted',
                AuthorisationForAuthorisedExaminerStatusCode::LAPSED => 'Lapsed',
                AuthorisationForAuthorisedExaminerStatusCode::WITHDRAWN => 'Withdrawn',
                AuthorisationForAuthorisedExaminerStatusCode::SURRENDERED => 'Surrendered',
            ]);
    }

    /**
     * @dataProvider validData
     */
    public function testFormForValidData(array $data)
    {
        $form = new StatusPropertyForm(new AuthForAuthorisedExaminerStatusCatalog($this->catalog));
        $form->setData($data);

        $this->assertTrue($form->isValid());
        $this->assertCount(0, $form->getMessages());
    }

    /**
     * @dataProvider invalidData
     */
    public function testFormReturnsErrorMsgForInvalidData(array $data)
    {
        $form = new StatusPropertyForm(new AuthForAuthorisedExaminerStatusCatalog($this->catalog));
        $form->setData($data);

        $this->assertFalse($form->isValid());
        $this->assertCount(1, $form->getMessages());

        $messages = $form->getStatusElement()->getMessages();
        $this->assertGreaterThanOrEqual(1, count($messages));
        $this->assertEquals(StatusPropertyForm::STATUS_EMPTY_MSG, array_shift($messages));
    }

    public function invalidData()
    {
        return [
            [[StatusPropertyForm::FIELD_STATUS => '']],
            [[StatusPropertyForm::FIELD_STATUS => ' ']],
        ];
    }

    public function validData()
    {
        return [
            [[StatusPropertyForm::FIELD_STATUS => AuthorisationForAuthorisedExaminerStatusCode::APPLIED]],
            [[StatusPropertyForm::FIELD_STATUS => AuthorisationForAuthorisedExaminerStatusCode::RETRACTED]],
            [[StatusPropertyForm::FIELD_STATUS => AuthorisationForAuthorisedExaminerStatusCode::WITHDRAWN]],
        ];
    }
}
