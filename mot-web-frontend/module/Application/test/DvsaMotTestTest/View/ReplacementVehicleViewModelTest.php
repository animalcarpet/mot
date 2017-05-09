<?php

namespace DvsaMotTestTest\View;

use DvsaCommon\Date\DateTimeDisplayFormat;
use DvsaMotTest\View\ReplacementMakeViewModel;
use DvsaMotTest\View\ReplacementModelViewModel;
use DvsaMotTest\View\ReplacementVehicleViewModel;
use PHPUnit_Framework_TestCase;

class ReplacementVehicleViewModelTest extends PHPUnit_Framework_TestCase
{
    public function testDataSetForViewModelReturnsValuesAsRequired()
    {
        $data = $this->getData();

        $viewModel = new ReplacementVehicleViewModel($data);

        $this->assertEquals($viewModel->getVin(), $data['vin']);
        $this->assertEquals($viewModel->getVrm(), $data['vrm']);
        $this->assertEquals($viewModel->getPrimaryColourId(), $data['primaryColour']['code']);
        $this->assertEquals($viewModel->getSecondaryColourId(), $data['secondaryColour']['code']);
        $this->assertEquals($viewModel->getMake(), new ReplacementMakeViewModel($data['make']));
        $this->assertEquals($viewModel->getModel(), new ReplacementModelViewModel($data['model']));
        $this->assertEquals($viewModel->getCountryOfRegistration(), $data['countryOfRegistration']);
        $this->assertEquals($viewModel->getExpiryDate(), $data['expiryDate']);
        $this->assertEquals($viewModel->getExpiryDisplayDate(), DateTimeDisplayFormat::date(new \DateTime($data['expiryDate'])));
        $this->assertEquals($viewModel->getExpiryDateYear(), '2015');
        $this->assertEquals($viewModel->getExpiryDateMonth(), '02');
        $this->assertEquals($viewModel->getExpiryDateDay(), '01');
        $this->assertTrue($viewModel->isLatestPassedMotTest());
    }

    private function getData()
    {
        return [
            'primaryColour' => [
                'code' => 'A',
            ],
            'secondaryColour' => [
                'code' => 'B',
            ],
            'make' => [
                'id' => 1,
                'code' => 'F',
                'name' => 'Ford',
            ],
            'model' => [
                'id' => 2,
                'code' => 'M',
                'name' => 'Mondeo',
            ],
            'countryOfRegistration' => 'GB',
            'expiryDate' => '2015-02-01',
            'vin' => '123',
            'vrm' => '123454',
            'isLatestPassedMotTest' => true,
        ];
    }
}
