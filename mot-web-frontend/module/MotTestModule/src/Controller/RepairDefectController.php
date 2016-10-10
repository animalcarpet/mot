<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link https://gitlab.motdev.org.uk/mot/mot
 */

namespace Dvsa\Mot\Frontend\MotTestModule\Controller;

use Dvsa\Mot\Frontend\MotTestModule\View\DefectsJourneyUrlGenerator;
use Dvsa\Mot\Frontend\MotTestModule\View\FlashMessageBuilder;
use DvsaCommon\HttpRestJson\Exception\GeneralRestException;
use DvsaCommon\UrlBuilder\MotTestUrlBuilder;
use DvsaMotTest\Controller\AbstractDvsaMotTestController;
use Zend\Http\Response;

/**
 * Class RepairDefectController.
 */
class RepairDefectController extends AbstractDvsaMotTestController
{
    /**
     * @var DefectsJourneyUrlGenerator
     */
    private $defectsJourneyUrlGenerator;

    /**
     * RepairDefectController constructor.
     *
     * @param DefectsJourneyUrlGenerator $defectsJourneyUrlGenerator
     */
    public function __construct(DefectsJourneyUrlGenerator $defectsJourneyUrlGenerator)
    {
        $this->defectsJourneyUrlGenerator = $defectsJourneyUrlGenerator;
    }

    /**
     * @return Response
     */
    public function repairAction()
    {
        $motTestNumber = (int) $this->params()->fromRoute('motTestNumber');
        $identifiedDefectId = (int) $this->params('identifiedDefectId');
        $identifiedDefectType = $this->getRequest()->getPost('defectType', 'defect');
        $identifiedDefectText = $this->getRequest()->getPost('defectText', '');

        try {
            $apiUrl = MotTestUrlBuilder::markDefectAsRepaired($motTestNumber, $identifiedDefectId)->toString();
            $this->getRestClient()->post($apiUrl);

            $this->addSuccessMessage(FlashMessageBuilder::defectRepairedSuccessfully($identifiedDefectType, $identifiedDefectText));

        } catch (GeneralRestException $e) {
            $this->addErrorMessage(FlashMessageBuilder::defectRepairedUnsuccessfully($identifiedDefectType, $identifiedDefectText));

        }

        return $this->redirect()->toUrl($this->defectsJourneyUrlGenerator->goBack());
    }

    /**
     * @return Response
     */
    public function undoRepairAction()
    {
        $motTestNumber = (int) $this->params()->fromRoute('motTestNumber');
        $identifiedDefectId = (int) $this->params('identifiedDefectId');
        $identifiedDefectType = $this->getRequest()->getPost('defectType', 'defect');
        $identifiedDefectText = $this->getRequest()->getPost('defectText', '');

        try {
            $apiUrl = MotTestUrlBuilder::undoMarkDefectAsRepaired($motTestNumber, $identifiedDefectId)->toString();
            $this->getRestClient()->post($apiUrl);

            $this->addSuccessMessage(FlashMessageBuilder::undoDefectRepairSuccessfully($identifiedDefectType, $identifiedDefectText));

        } catch (GeneralRestException $e) {
            $this->addErrorMessage(FlashMessageBuilder::undoDefectRepairUnsuccessfully($identifiedDefectType, $identifiedDefectText));
        }

        return $this->redirect()->toUrl($this->defectsJourneyUrlGenerator->goBack());
    }
}
