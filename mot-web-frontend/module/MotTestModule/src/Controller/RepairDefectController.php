<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link https://gitlab.motdev.org.uk/mot/mot
 */

namespace Dvsa\Mot\Frontend\MotTestModule\Controller;

use Dvsa\Mot\Frontend\MotTestModule\View\DefectsJourneyUrlGenerator;
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
        $motTestNumber = (int) $this->params('motTestNumber', 0);
        $identifiedDefectId = (int) $this->getRequest()->getPost('defectId', 0);
        $identifiedDefectType = $this->getRequest()->getPost('defectType', 'defect');
        $identifiedDefectText = $this->getRequest()->getPost('defectText', '');

        try {
            $apiUrl = MotTestUrlBuilder::reasonForRejection($motTestNumber, $identifiedDefectId)->toString();
            $this->getRestClient()->delete($apiUrl);

            $this->addSuccessMessage(
                sprintf(
                    'The %s <strong>%s</strong> has been removed',
                    $identifiedDefectType,
                    $identifiedDefectText
                )
            );
        } catch (\Exception $e) {
            $this->addErrorMessage(
                sprintf(
                    'The %s <strong>%s</strong> has not been removed. Try again',
                    $identifiedDefectType,
                    $identifiedDefectText
                )
            );
        }

        return $this->redirect()->toUrl($this->defectsJourneyUrlGenerator->goBack());
    }
}
