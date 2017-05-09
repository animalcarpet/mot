<?php

namespace SiteTest\UpdateVtsProperty\Form;

use Application\Service\CatalogService;
use Core\Catalog\Vts\VtsTypeCatalog;
use DvsaCommonTest\TestUtils\XMock;
use Site\UpdateVtsProperty\Process\Form\TypePropertyForm;
use DvsaCommon\Enum\SiteTypeCode;

class TypePropertyFormTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider validData
     */
    public function testFormForValidData(array $data)
    {
        /** @var VtsTypeCatalog $catalog */
        $catalog = XMock::of(CatalogService::class);

        $catalog->expects($this->any())
            ->method('getSiteTypes')
            ->willReturn(
                [
                    SiteTypeCode::VEHICLE_TESTING_STATION => 'Vehicle testing station',
                    SiteTypeCode::AREA_OFFICE => 'Area Office',
                    SiteTypeCode::TRAINING_CENTRE => 'Training Center',
                ]
            );

        $form = new TypePropertyForm(new VtsTypeCatalog($catalog));
        $form->setData($data);

        $this->assertTrue($form->isValid());
        $this->assertCount(0, $form->getMessages());
    }

    public function validData()
    {
        return [
            [[TypePropertyForm::FIELD_TYPE => SiteTypeCode::AREA_OFFICE]],
            [[TypePropertyForm::FIELD_TYPE => SiteTypeCode::VEHICLE_TESTING_STATION]],
            [[TypePropertyForm::FIELD_TYPE => SiteTypeCode::TRAINING_CENTRE]],
        ];
    }

    /**
     * @dataProvider invalidData
     */
    public function testFormReturnsErrorMsgForInvalidData(array $data)
    {
        /** @var VtsTypeCatalog $catalog */
        $catalog = XMock::of(CatalogService::class);

        $catalog->expects($this->any())
            ->method('getSiteTypes')
            ->willReturn(
                [
                    SiteTypeCode::VEHICLE_TESTING_STATION => 'Vehicle testing station',
                    SiteTypeCode::AREA_OFFICE => 'Area Office',
                    SiteTypeCode::TRAINING_CENTRE => 'Training Center',
                ]
            );

        $form = new TypePropertyForm(new VtsTypeCatalog($catalog));
        $form->setData($data);

        $this->assertFalse($form->isValid());
        $this->assertCount(1, $form->getMessages());

        $msgs = $form->getTypeElement()->getMessages();
        $this->assertCount(1, $msgs);
        $this->assertEquals(TypePropertyForm::TYPE_EMPTY_MSG, array_shift($msgs));
    }

    public function invalidData()
    {
        return [
            [[TypePropertyForm::FIELD_TYPE => '']],
            [[]],
        ];
    }
}
