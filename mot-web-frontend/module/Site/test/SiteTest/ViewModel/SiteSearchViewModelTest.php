<?php

namespace SiteTest\ViewModel;

use DvsaCommon\Dto\Search\SiteSearchParamsDto;
use DvsaCommon\UrlBuilder\SiteUrlBuilderWeb;
use Report\Table\Table;
use Site\ViewModel\SiteSearchViewModel;
use Zend\Mvc\Controller\Plugin\FlashMessenger;

/**
 * Class SiteSearchViewModelTest.
 */
class SiteSearchViewModelTest extends \PHPUnit_Framework_TestCase
{
    const SITE_NUMBER = 'V1';
    const SITE_NAME = 'Garage';
    const SITE_TOWN = 'Toulouse';
    const SITE_POSTCODE = 'AAA ZZZ';

    public function testGetterSetter()
    {
        $model = new SiteSearchViewModel();

        $model->populateFromQuery(
            [
                SiteSearchParamsDto::SITE_NUMBER => self::SITE_NUMBER,
                SiteSearchParamsDto::SITE_NAME => self::SITE_NAME,
                SiteSearchParamsDto::SITE_TOWN => self::SITE_TOWN,
                SiteSearchParamsDto::SITE_POSTCODE => self::SITE_POSTCODE,
                SiteSearchParamsDto::SITE_VEHICLE_CLASS => [],
            ]
        );

        $table = (new Table())
            ->setRowsTotalCount(1);
        $this->assertInstanceOf(SiteSearchViewModel::class, $model->setTable($table));
        $this->assertInstanceOf(Table::class, $model->getTable());
        $this->assertEquals(1, $model->getTotalResults());
        $this->assertEquals(self::SITE_NUMBER, $model->getSiteNumber());
        $this->assertEquals(self::SITE_NAME, $model->getSiteName());
        $this->assertEquals(self::SITE_TOWN, $model->getSiteTown());
        $this->assertEquals(self::SITE_POSTCODE, $model->getSitePostcode());
        $this->assertEquals([], $model->getSiteVehicleClass());
        $this->assertEquals($this->getSiteVehicleClassParameters(), $model->getSiteVehicleClassParameters());

        $this->assertEquals('V1, Garage, Toulouse, AAA ZZZ', $model->displaySearchCriteria());

        $this->assertInstanceOf(SiteSearchParamsDto::class, $model->prepareSearchParams());

        $this->assertEquals(
            SiteUrlBuilderWeb::search().'?'.http_build_query($model->toArray()),
            $model->getSearchPage()
        );
        $this->assertEquals(SiteUrlBuilderWeb::result(), $model->getResultPage());

        $this->assertTrue($model->isValid());
    }

    public function testInvalidForm()
    {
        $model = (new SiteSearchViewModel())
            ->setSiteNumber('S')
            ->setSiteName('N')
            ->setSiteTown('T')
            ->setSitePostcode('P');

        $this->assertFalse($model->isValid());
        $this->assertEquals(SiteSearchViewModel::NOT_ENOUGH_CHAR, $model->getError(SiteSearchParamsDto::SITE_NUMBER));
        $this->assertEquals(SiteSearchViewModel::NOT_ENOUGH_CHAR, $model->getError(SiteSearchParamsDto::SITE_NAME));
        $this->assertEquals(SiteSearchViewModel::NOT_ENOUGH_CHAR, $model->getError(SiteSearchParamsDto::SITE_TOWN));
        $this->assertEquals(SiteSearchViewModel::NOT_ENOUGH_CHAR, $model->getError(SiteSearchParamsDto::SITE_POSTCODE));
    }

    public function testInvalidFormClass()
    {
        $flash = new FlashMessenger();
        $model = new SiteSearchViewModel();

        $model->setSiteVehicleClass([1]);
        $this->assertTrue($model->isSiteVehicleClassChecked(1));
        $this->assertTrue($model->isFormEmpty($flash));
        $this->assertEquals(
            [SiteSearchViewModel::ONLY_VEHICLE_CLASS],
            $flash->getCurrentErrorMessages()
        );
    }

    public function testEmptyForm()
    {
        $flash = new FlashMessenger();
        $model = new SiteSearchViewModel();

        $this->assertTrue($model->isFormEmpty($flash));
        $this->assertEquals(
            [SiteSearchViewModel::ONE_FIELD_REQUIRED],
            $flash->getCurrentErrorMessages()
        );
    }

    private function getSiteVehicleClassParameters()
    {
        return [
            [
                'value' => '1',
                'inputName' => SiteSearchParamsDto::SITE_VEHICLE_CLASS.'[]',
                'key' => 'Class 1',
                'checked' => false,
            ],
            [
                'value' => '2',
                'inputName' => SiteSearchParamsDto::SITE_VEHICLE_CLASS.'[]',
                'key' => 'Class 2',
                'checked' => false,
            ],
            [
                'value' => '3',
                'inputName' => SiteSearchParamsDto::SITE_VEHICLE_CLASS.'[]',
                'key' => 'Class 3',
                'checked' => false,
            ],
            [
                'value' => '4',
                'inputName' => SiteSearchParamsDto::SITE_VEHICLE_CLASS.'[]',
                'key' => 'Class 4',
                'checked' => false,
            ],
            [
                'value' => '5',
                'inputName' => SiteSearchParamsDto::SITE_VEHICLE_CLASS.'[]',
                'key' => 'Class 5',
                'checked' => false,
            ],
            [
                'value' => '7',
                'inputName' => SiteSearchParamsDto::SITE_VEHICLE_CLASS.'[]',
                'key' => 'Class 7',
                'checked' => false,
            ],
        ];
    }
}
