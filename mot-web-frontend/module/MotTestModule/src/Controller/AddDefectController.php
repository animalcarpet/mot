<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link https://gitlab.motdev.org.uk/mot/mot
 */

namespace Dvsa\Mot\Frontend\MotTestModule\Controller;

use Core\Controller\AbstractAuthActionController;
use Zend\View\Model\ViewModel;

class AddDefectController extends AbstractAuthActionController
{
    /**
     * Handles the screen for adding a defect.
     *
     * See https://mot-rfr-production.herokuapp.com/rfr/defect?l1=0&l2=0&l3=undefined&l4=undefined&rfrIndex=0&type=Advisory
     *
     * @return ViewModel
     */
    public function addAction()
    {
        $motTestNumber = $this->params()->fromRoute('motTestNumber');
        $categoryId = $this->params()->fromRoute('categoryId', 'N/A');
        $defectId = $this->params()->fromRoute('defectId');
        // NOTE: "type" is also known as "severity" in the routes spreadsheet.
        $type = $this->params()->fromRoute('type');

        switch (strtolower($type)) {
            case 'advisory':
                $title = 'Add an advisory';
                break;
            case 'failure':
                $title = 'Add a failure';
                break;
            case 'prs':
                $title = 'Add a PRS';
                break;
            default:
                $title = 'Add a(n) ' . $type;
        }

        $this->enableGdsLayout($title, '');

        return $this->createViewModel('defects/add-defect.twig', [
            'motTestNumber' => $motTestNumber,
            'categoryId' => $categoryId,
            'defectId' => $defectId,
            'type' => $type,
            'storyId' => 'BL-1952',
        ]);
    }

    /**
     * @param string $template
     * @param array  $variables
     *
     * @return ViewModel
     */
    private function createViewModel($template, array $variables)
    {
        $viewModel = new ViewModel();
        $viewModel->setTemplate($template);
        $viewModel->setVariables($variables);

        return $viewModel;
    }
}
