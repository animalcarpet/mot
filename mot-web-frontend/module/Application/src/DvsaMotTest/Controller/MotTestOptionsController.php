<?php

namespace DvsaMotTest\Controller;

use DvsaClient\Mapper\VehicleExpiryMapper;
use DvsaCommon\Date\DateTimeDisplayFormat;
use DvsaCommon\Dto\MotTesting\MotTestOptionsDto;
use DvsaCommon\Enum\MotTestTypeCode;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use DvsaCommon\UrlBuilder\UrlBuilder;
use DvsaMotTest\Service\MotChecklistPdfService;
use DvsaMotTest\Presenter\MotTestOptionsPresenter;
use Zend\View\Model\ViewModel;

class MotTestOptionsController extends AbstractDvsaMotTestController implements AutoWireableInterface
{
    const ROUTE_MOT_TEST_OPTIONS = 'mot-test/options';

    const TEMPLATE_MOT_TEST_OPTIONS = 'dvsa-mot-test/mot-test/mot-test-options.phtml';

    const PAGE_TITLE_TEMPLATE = '%s started';

    protected $motChecklistPdfService;
    private $vehicleExpiryMapper;

    public function __construct(
        MotChecklistPdfService $motChecklistPdfService,
        VehicleExpiryMapper $vehicleExpiryMapper
    ) {
        $this->motChecklistPdfService = $motChecklistPdfService;
        $this->vehicleExpiryMapper = $vehicleExpiryMapper;
    }

    public function motTestOptionsAction()
    {
        $motTestNumber = $this->params()->fromRoute('motTestNumber');

        $dto = MotTestOptionsDto::fromArray(
            $this->getRestClient()->get(UrlBuilder::motTestOptions($motTestNumber)->toString())['data']
        );

        $presenter = new MotTestOptionsPresenter($dto);
        $presenter->setMotTestNumber($motTestNumber);

        $pageTitle = sprintf(self::PAGE_TITLE_TEMPLATE, $presenter->getReadableMotTestType());

        $this->layout()->setVariable('pageTitle', $pageTitle);
        $this->layout()->setVariable('pageSubTitle', $presenter->getPageSubTitle());

        $this->addMotTestLateInfoToGtmDataLayer($dto->getVehicleId(), $dto->getVehicleRegistrationNumber());

        $viewModel = new ViewModel(['presenter' => $presenter]);
        $viewModel->setTemplate(self::TEMPLATE_MOT_TEST_OPTIONS);

        return $viewModel;
    }

    public function motChecklistAction()
    {
        $motTestNumber = $this->params()->fromRoute('motTestNumber');
        $pdf = $this->motChecklistPdfService->getChecklistPdf($motTestNumber);
        $this->getResponse()
            ->setContent($pdf)
            ->getHeaders()->addHeaders([
                'Content-Length'        =>  strlen($pdf),
                'Content-Type'          => 'application/pdf',
                'Content-Disposition'   => 'filename=Mot-checklist_' . $motTestNumber . '.pdf',
            ]);

        return $this->getResponse();
    }

    private function addMotTestLateInfoToGtmDataLayer($vehicleId, $vrm)
    {
        $gtmData = [];

        $gtmData['vrm'] = $vrm;
        $today = new \DateTime();
        $expiryDate = $this->vehicleExpiryMapper->getExpiryForVehicle($vehicleId)->getExpiryDate();
        $gtmData['expiryDate'] = DateTimeDisplayFormat::dateTime($expiryDate);

        $gtmData['testLate'] = $today > $expiryDate;

        if($gtmData['testLate']) {
            $late = $today->diff($expiryDate);
            $gtmData['testLateInDays'] = $late->days;
        }

        $this->gtmDataLayer($gtmData);
    }
}
