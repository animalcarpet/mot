<?php

namespace OrganisationTest\UpdateAeProperty\Form;

use Application\Service\CatalogService;
use Core\Catalog\Organisation\OrganisationCompanyTypeCatalog;
use DvsaCommon\Enum\CompanyTypeCode;
use DvsaCommonTest\TestUtils\XMock;
use Organisation\UpdateAeProperty\Process\Form\BusinessTypePropertyForm;

class BusinessTypePropertyFormTest extends \PHPUnit_Framework_TestCase
{
    protected $catalog;

    public function setUp()
    {
        $this->catalog = XMock::of(CatalogService::class);

        $this->catalog->expects($this->any())
            ->method('getOrganisationCompanyTypes')
            ->willReturn([
                CompanyTypeCode::COMPANY => 'Company',
                CompanyTypeCode::PARTNERSHIP => 'Partnership',
                CompanyTypeCode::PUBLIC_BODY => 'Public Body',
                CompanyTypeCode::SOLE_TRADER => 'Sole Trader',
            ]);
    }
    /**
     * @dataProvider validData
     */
    public function testFormForValidData(array $data)
    {
        $form = new BusinessTypePropertyForm(new OrganisationCompanyTypeCatalog($this->catalog));
        $form->setData($data);

        $this->assertTrue($form->isValid());
        $this->assertCount(0, $form->getMessages());
    }

    public function validData()
    {
        return [
            [
                [
                    BusinessTypePropertyForm::FIELD_COMPANY_NUMBER => $this->createName(BusinessTypePropertyForm::FIELD_NAME_MAX_LENGTH),
                    BusinessTypePropertyForm::FIELD_TYPE => CompanyTypeCode::COMPANY,
                ],
                true,
            ],
            [
                [
                    BusinessTypePropertyForm::FIELD_COMPANY_NUMBER => $this->createName(BusinessTypePropertyForm::FIELD_NAME_MAX_LENGTH + 10),
                    BusinessTypePropertyForm::FIELD_TYPE => CompanyTypeCode::PARTNERSHIP,
                ],
                true,
            ],
            [
                [
                    BusinessTypePropertyForm::FIELD_COMPANY_NUMBER => '',
                    BusinessTypePropertyForm::FIELD_TYPE => CompanyTypeCode::PUBLIC_BODY,
                ],
                true,
            ],
        ];
    }

    /**
     * @dataProvider invalidData
     */
    public function testFormReturnsErrorMsgForInvalidData(array $data)
    {
        $form = new BusinessTypePropertyForm(new OrganisationCompanyTypeCatalog($this->catalog));
        $form->setData($data);

        $this->assertFalse($form->isValid());
        $this->assertCount(1, $form->getMessages());
    }

    public function invalidData()
    {
        return [
            [
                [
                    BusinessTypePropertyForm::FIELD_COMPANY_NUMBER => '',
                    BusinessTypePropertyForm::FIELD_TYPE => CompanyTypeCode::COMPANY,
                ],
            ],
            [
                [
                    BusinessTypePropertyForm::FIELD_COMPANY_NUMBER => '',
                    BusinessTypePropertyForm::FIELD_TYPE => 'business type is not in the haystack',
                ],
            ],
            [
                [
                    BusinessTypePropertyForm::FIELD_COMPANY_NUMBER => str_repeat(' ', 6),
                    BusinessTypePropertyForm::FIELD_TYPE => 'business type is not in the haystack',
                ],
            ],
            [
                [
                    BusinessTypePropertyForm::FIELD_COMPANY_NUMBER => str_repeat(' ', BusinessTypePropertyForm::FIELD_NAME_MAX_LENGTH + 1),
                    BusinessTypePropertyForm::FIELD_TYPE => 'business type is not in the haystack',
                ],
            ],
            [
                [
                    BusinessTypePropertyForm::FIELD_COMPANY_NUMBER => $this->createName(BusinessTypePropertyForm::FIELD_NAME_MAX_LENGTH + 1),
                    BusinessTypePropertyForm::FIELD_TYPE => CompanyTypeCode::COMPANY,
                ],
            ],
            [
                [
                    BusinessTypePropertyForm::FIELD_COMPANY_NUMBER => null,
                    BusinessTypePropertyForm::FIELD_TYPE => null,
                ],
            ],
        ];
    }

    private function createName($length, $char = 'X')
    {
        $name = '';
        while ($length) {
            $name .= $char;
            --$length;
        }

        return $name;
    }
}
