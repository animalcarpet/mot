<?php

namespace Dvsa\Mot\Frontend\PersonModuleTest\ViewModel;

use Core\ViewModel\Gds\Table\GdsTable;
use Dvsa\Mot\Frontend\PersonModule\ViewModel\QualificationDetailsGroupViewModel;
use DvsaCommon\ApiClient\Person\MotTestingCertificate\Dto\MotTestingCertificateDto;
use DvsaCommon\Enum\AuthorisationForTestingMotStatusCode;
use DvsaCommon\Enum\VehicleClassGroupCode;
use DvsaCommon\Model\TesterGroupAuthorisationStatus;

/**
 * Class QualificationDetailsGroupViewModelTest.
 */
class QualificationDetailsGroupViewModelTest extends \PHPUnit_Framework_TestCase
{
    const CHANGE_URL = 'changeUrl';
    const ADD_URL = 'addUrl';
    const REMOVE_URL = 'removeUrl';
    const AUTHORISATION_STATUS_NAME = 'Mocked name';
    const DATE_OF_QUALIFICATION = '10-10-2000';
    const CERTIFICATE_NUMBER = 'W123';

    /** @var MotTestingCertificateDto */
    private $motTestingCertificateDtoMock;
    /** @var TesterGroupAuthorisationStatus */
    private $testerGroupAuthorisationStatus;
    /** @var QualificationDetailsGroupViewModel */
    private $viewModel;

    public function setUp()
    {
        $this->testerGroupAuthorisationStatus = $this
            ->getMockBuilder(TesterGroupAuthorisationStatus::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->motTestingCertificateDtoMock = $this->createMotTestingCertificateDtoMock();
    }

    private function createViewModel()
    {
        $this->viewModel = new QualificationDetailsGroupViewModel(
            VehicleClassGroupCode::BIKES,
            $this->testerGroupAuthorisationStatus,
            $this->motTestingCertificateDtoMock,
            self::CHANGE_URL,
            self::ADD_URL,
            self::REMOVE_URL
        );

        return $this->viewModel;
    }

    private function createMotTestingCertificateDtoMock()
    {
        $dto = new MotTestingCertificateDto();
        $dto->setDateOfQualification(self::DATE_OF_QUALIFICATION)
            ->setCertificateNumber(self::CERTIFICATE_NUMBER);

        return $dto;
    }

    /**
     * @dataProvider dataProviderTestGetTable
     */
    public function testGetTable($dto, $authorisationStatusCode, $expectedActionLinks, $expectedCertificate)
    {
        $this->testerGroupAuthorisationStatus
            ->method('getCode')
            ->willReturn($authorisationStatusCode);
        $this->testerGroupAuthorisationStatus
            ->method('getName')
            ->willReturn(self::AUTHORISATION_STATUS_NAME);

        //GIVEN certificate for class is provided or not
        $this->motTestingCertificateDtoMock = $dto;
        $this->createViewModel();

        //WHEN I get table for certificate
        /** @var GdsTable $table */
        $table = $this->viewModel->getTable('class-name');

        //THEN table has 2 rows
        $this->assertSame(2, count($table->getRows()));

        //AND qualification status row is displayed with correct data
        $firstRow = $table->getRow(0);
        $this->assertSame('Qualification status', $firstRow->getLabel()->getContent());
        $this->assertSame(self::AUTHORISATION_STATUS_NAME, $firstRow->getValue()->getContent());

        //AND certificate details row is displayed with correct data
        $secondRow = $table->getRow(1);
        $this->assertSame('Certificate', $secondRow->getLabel()->getContent());
        $this->assertSame($expectedCertificate['number'], $secondRow->getValue()->getContent());
        $this->assertSame($expectedCertificate['date'], $secondRow->getValueMetaData()->getContent());

        //AND expected action links appear
        foreach ($expectedActionLinks as $key => $expectedActionLink) {
            $this->assertSame($expectedActionLink, $secondRow->getActionLink($key)->getUrl());
        }
    }

    public function dataProviderTestGetTable()
    {
        $dto = $this->createMotTestingCertificateDtoMock();

        return [
            //Certificate does not exist, add certificate is only link to be displayed
            [
                'dto' => null,
                'authorisationStatusCode' => AuthorisationForTestingMotStatusCode::INITIAL_TRAINING_NEEDED,
                'expectedActionLinks' => [
                    self::ADD_URL,
                ],
                'expectedCertificate' => [
                    'number' => ' ',
                    'date' => null,
                ],
            ],
            //Certificate exists, we display add, change, remove links
            [
                'dto' => $dto,
                'authorisationStatusCode' => AuthorisationForTestingMotStatusCode::INITIAL_TRAINING_NEEDED,
                'expectedActionLinks' => [
                    self::CHANGE_URL,

                    self::REMOVE_URL,

                    self::ADD_URL,

                ],
                'expectedCertificate' => [
                    'number' => self::CERTIFICATE_NUMBER,
                    'date' => 'Awarded 10 October 2000',
                ],
            ],
            //Certificate does not exist, tester is already qualified
            [
                'dto' => null,
                'authorisationStatusCode' => AuthorisationForTestingMotStatusCode::QUALIFIED,
                'expectedActionLinks' => [],
                'expectedCertificate' => [
                    'number' => 'Not needed',
                    'date' => 'Before 1 April 2016',
                ],
            ],
        ];
    }
}
