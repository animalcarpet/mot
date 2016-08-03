<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link https://gitlab.motdev.org.uk/mot/mot
 */

namespace Dvsa\Mot\Frontend\MotTestModule\Controller;

use Core\Controller\AbstractAuthActionController;
use Zend\View\Model\ViewModel;

class AddManualAdvisoryController extends AbstractAuthActionController
{
    /**
     * Handles the screen for adding a manual advisory.
     *
     * Heroku screen not yet ready.
     *
     * @return ViewModel
     */
    public function addAction()
    {
        $motTestNumber = $this->params()->fromRoute('motTestNumber');
        $categoryId = $this->params()->fromRoute('categoryId');
        $type = 'advisory';

        $this->enableGdsLayout('Add a manual advisory', '');

        return $this->createViewModel('defects/add-defect.twig', [
            'motTestNumber' => $motTestNumber,
            'categoryId' => $categoryId,
            'defectId' => 'N/A',
            'type' => $type,
            'storyId' => 'BL-2421',
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
