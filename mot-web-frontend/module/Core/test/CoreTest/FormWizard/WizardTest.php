<?php

namespace CoreTest\FormWizard;

use Core\FormWizard\StepList;
use Core\FormWizard\Wizard;
use CoreTest\FormWizard\Fake\FakeStep;

class WizardTest extends \PHPUnit_Framework_TestCase
{
    const FIRST_NAME_STEP = 'first step name';
    const SECOND_NAME_STEP = 'second step name';
    const THIRD_NAME_STEP = 'third step name';

    private $formUuid = 'UUUU-IIII-DDDD';

    /**
     * @dataProvider invalidStepList
     */
    public function testWizardDoesNotAllowGoToStepIfOneOfPreviousStepIsInvalid(StepList $stepList, $currentStepName, $isPost, $expectedResponse)
    {
        $wizard = new Wizard($stepList);
        $response = $wizard->process($currentStepName, $isPost, $this->formUuid);

        $this->assertEquals($expectedResponse, $response);
    }

    public function invalidStepList()
    {
        $stepListWithInvalidFirstStep = $this->createStepListWithInvalidFirstStep();
        $stepListWithInvalidSecondStep = $this->createStepListWithInvalidSecondStep();

        return [
            [
                $stepListWithInvalidFirstStep,
                self::FIRST_NAME_STEP,
                true,
                $stepListWithInvalidFirstStep->get(self::FIRST_NAME_STEP)->executePost([]),
            ],
            [
                $stepListWithInvalidFirstStep,
                self::SECOND_NAME_STEP,
                true,
                $stepListWithInvalidFirstStep->get(self::FIRST_NAME_STEP)->getRoute(['formUuid' => $this->formUuid]),
            ],
            [
                $stepListWithInvalidFirstStep,
                self::THIRD_NAME_STEP,
                true,
                $stepListWithInvalidFirstStep->get(self::FIRST_NAME_STEP)->getRoute(['formUuid' => $this->formUuid]),
            ],
            [
                $stepListWithInvalidFirstStep,
                self::FIRST_NAME_STEP,
                false,
                $stepListWithInvalidFirstStep->get(self::FIRST_NAME_STEP)->executeGet(),
            ],
            [
                $stepListWithInvalidFirstStep,
                self::SECOND_NAME_STEP,
                false,
                $stepListWithInvalidFirstStep->get(self::FIRST_NAME_STEP)->getRoute(['formUuid' => $this->formUuid]),
            ],
            [
                $stepListWithInvalidFirstStep,
                self::THIRD_NAME_STEP,
                false,
                $stepListWithInvalidFirstStep->get(self::FIRST_NAME_STEP)->getRoute(['formUuid' => $this->formUuid]),
            ],
            [
                $stepListWithInvalidSecondStep,
                self::FIRST_NAME_STEP,
                true,
                $stepListWithInvalidSecondStep->get(self::FIRST_NAME_STEP)->executePost([]),
            ],
            [
                $stepListWithInvalidSecondStep,
                self::SECOND_NAME_STEP,
                true,
                $stepListWithInvalidSecondStep->get(self::SECOND_NAME_STEP)->executePost([]),
            ],
            [
                $stepListWithInvalidFirstStep,
                self::THIRD_NAME_STEP,
                true,
                $stepListWithInvalidSecondStep->get(self::FIRST_NAME_STEP)->getRoute(['formUuid' => $this->formUuid]),
            ], [
                $stepListWithInvalidSecondStep,
                self::FIRST_NAME_STEP,
                false,
                $stepListWithInvalidSecondStep->get(self::FIRST_NAME_STEP)->executeGet(),
            ],
            [
                $stepListWithInvalidSecondStep,
                self::SECOND_NAME_STEP,
                false,
                $stepListWithInvalidSecondStep->get(self::SECOND_NAME_STEP)->executeGet(),
            ],
            [
                $stepListWithInvalidFirstStep,
                self::THIRD_NAME_STEP,
                false,
                $stepListWithInvalidSecondStep->get(self::FIRST_NAME_STEP)->getRoute(['formUuid' => $this->formUuid]),
            ],
        ];
    }

    private function createStepListWithInvalidFirstStep()
    {
        $firstStep = $this->createInvalidStep(self::FIRST_NAME_STEP);
        $secondStep = $this->createValidStep(self::SECOND_NAME_STEP);
        $thirdStep = $this->createValidStep(self::THIRD_NAME_STEP);

        $thirdStep->setPrevStep($secondStep);

        $secondStep
            ->setPrevStep($firstStep)
            ->setNextStep($thirdStep);

        $firstStep->setNextStep($firstStep);

        return new StepList([$firstStep->getName() => $firstStep, $thirdStep->getName() => $thirdStep, $secondStep->getName() => $secondStep]);
    }

    private function createStepListWithInvalidSecondStep()
    {
        $firstStep = $this->createValidStep(self::FIRST_NAME_STEP);
        $secondStep = $this->createInvalidStep(self::SECOND_NAME_STEP);
        $thirdStep = $this->createValidStep(self::THIRD_NAME_STEP);

        $thirdStep->setPrevStep($secondStep);

        $secondStep
            ->setPrevStep($firstStep)
            ->setNextStep($thirdStep);

        $firstStep->setNextStep($firstStep);

        return new StepList([$firstStep->getName() => $firstStep, $thirdStep->getName() => $thirdStep, $secondStep->getName() => $secondStep]);
    }

    private function createValidStep($name, $storedData = [])
    {
        return $this->createStep($name, true, $storedData);
    }

    private function createInvalidStep($name, $storedData = [])
    {
        return $this->createStep($name, false, $storedData);
    }

    private function createStep($name, $isValid, $storedData = [])
    {
        return new FakeStep($name, $isValid, $storedData);
    }
}
