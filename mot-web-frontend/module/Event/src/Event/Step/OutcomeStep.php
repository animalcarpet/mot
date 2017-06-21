<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */

namespace Event\Step;

use DvsaCommon\InputFilter\Event\OutcomeInputFilter;

class OutcomeStep extends AbstractEventStep
{
    /**
     * const used for the session key, available via getId().
     */
    const STEP_ID = 'OUTCOME';

    /**
     * @var
     */
    protected $outcomeCode;

    /**
     * @var
     */
    protected $notes;

    /**
     * @return string
     */
    public function getId()
    {
        return self::STEP_ID;
    }

    /**
     * Load the steps data from the session storage.
     *
     * @return array
     */
    public function load()
    {
        $values = $this->sessionService->load(self::STEP_ID);
        $this->readFromArray($values);

        return $this;
    }

    /**
     * @param array $values
     *
     * @return mixed
     */
    public function readFromArray(array $values)
    {
        if (is_array($values) && count($values)) {
            $this->setOutcomeCode($values[OutcomeInputFilter::FIELD_OUTCOME]);
            $this->setNotes($values[OutcomeInputFilter::FIELD_NOTES]);

            $this->filter->setData($this->toArray());
        }
    }

    /**
     * Export the step values as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return [
            OutcomeInputFilter::FIELD_OUTCOME => $this->getOutcomeCode(),
            OutcomeInputFilter::FIELD_NOTES => $this->getNotes(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function getCleanFilterWhiteList()
    {
        return [
            OutcomeInputFilter::FIELD_NOTES,
        ];
    }

    /**
     * The route for this step.
     *
     * @return mixed
     */
    public function route()
    {
        return 'event-manual-add/outcome';
    }

    /**
     * describes the steps progress in the event process.
     *
     * Step 1 of 3
     * Step 2 of 3
     * etc
     *
     * @return string|null
     */
    public function getProgress()
    {
        return 'Step 2 of 3';
    }

    /**
     * @return mixed
     */
    public function getOutcomeCode()
    {
        return $this->outcomeCode;
    }

    /**
     * @param mixed $outcomeCode
     */
    public function setOutcomeCode($outcomeCode)
    {
        $this->outcomeCode = $outcomeCode;
    }

    /**
     * @return mixed
     */
    public function getNotes()
    {
        return $this->notes;
    }

    /**
     * @param mixed $notes
     */
    public function setNotes($notes)
    {
        $this->notes = $notes;
    }
}
