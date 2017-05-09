<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */

namespace Core\Step;

use Core\Service\SessionService;
use Zend\InputFilter\InputFilter;

/**
 * Base class for Steps.
 */
abstract class AbstractStep implements Step
{
    /**
     * @var SessionService
     */
    protected $sessionService;

    /**
     * @var InputFilter
     */
    protected $filter;

    /**
     * @param SessionService $sessionService
     */
    public function __construct(SessionService $sessionService, InputFilter $filter)
    {
        $this->sessionService = $sessionService;
        $this->filter = $filter;
    }

    /**
     * @param array $values
     *
     * @return bool
     */
    public function validate(array $values = [])
    {
        $values = count($values) ? $values : $this->toArray();
        $values = $this->clean($values);
        $this->filter->init();
        $this->filter->setData($values);

        return $this->filter->isValid();
    }

    /**
     * The route params for this step.
     *
     * @return array
     */
    public function routeParams()
    {
        return [];
    }

    /**
     * list of fields which must be filtered by the clean function to remove whitespace.
     *
     * @return array
     */
    abstract protected function getCleanFilterWhiteList();

    /**
     * Save the steps data to the session storage.
     *
     * @param bool|true $validate
     *
     * @return bool
     */
    public function save($validate = true)
    {
        // Cache the results because isValid performs the full validation on each call.
        if ($validate === true && $this->filter->isValid() === false) {
            return false;
        }

        $this->saveToSession();

        return true;
    }

    /**
     * @return array
     */
    public function toViewArray()
    {
        $values = $this->toArray();

        $result = array_merge([], $values);
        $result['errors'] = $this->filter->getMessages();
        $result['errorsSummary'] = $this->getErrorsSummary();
        $result['isValid'] = count($result['errors']) === 0;

        // Automatically make error check variables available to the views.
        // This removes the need for Array::get() checks inside the views, and makes them safer and cleaner.
        foreach ($values as $fieldName => $value) {
            $result[$fieldName.'HasError'] = isset($result['errors'][$fieldName]);
        }

        $result['urls'] = [];

        return $result;
    }

    /**
     * describes the steps progress in the registration process.
     *
     * Step 1 of 6
     * Step 2 of 6
     * etc
     *
     * @return string|null
     */
    public function getProgress()
    {
        return;
    }

    /**
     * Clean whitespace before and after value.
     *
     * @param array $values
     *
     * @return array
     */
    public function clean(array $values)
    {
        $array = [];
        $blacklist = array_flip($this->getCleanFilterWhiteList());

        // If black list is empty no need to whitelist anything.
        if (empty($blacklist)) {
            return $values;
        }

        foreach ($values as $key => $value) {
            if (array_key_exists($key, $blacklist)) {
                $array[$key] = trim($value);
            } else {
                $array[$key] = $value;
            }
        }

        return $array;
    }

    /**
     * A function that must be implemented in the step. This is how
     * we enforce that the logic of what is saved within a step, stays within that step.
     */
    protected function saveToSession()
    {
        $this->sessionService->save(static::STEP_ID, $this->clean($this->toArray()));
    }

    /**
     * Preparing validation messages with expected format for summary boxes.
     *
     * @return array
     */
    protected function getErrorsSummary()
    {
        $genericErrors = $this->filter->getMessages();
        $errorsSummary = [];

        foreach ($genericErrors as $fieldName => $messages) {
            $errorsSummary[$fieldName] = [];

            foreach ($messages as $validator => $message) {
                $errorsSummary[$fieldName][$validator] = $this->prependLabel($fieldName, $message);
            }
        }

        return $errorsSummary;
    }

    /**
     * @param string $fieldName the field name known to the filter input
     * @param string $message   the original validation message provided by the filter input
     *
     * @return string expected format which is prepended by the input's label (html DOM)
     */
    protected function prependLabel($fieldName, $message)
    {
        return $this->getFieldLabel($fieldName).' - '.$message;
    }

    /**
     * To guess or map the label's value (name).
     *
     * @param string $fieldName
     *
     * @return string
     */
    protected function getFieldLabel($fieldName)
    {
        $fieldNameMapping = $this->getFieldNameMapping();

        if (array_key_exists($fieldName, $fieldNameMapping)) {
            return $fieldNameMapping[$fieldName];
        }

        return ucfirst(strtolower(preg_replace('/(?!^)[A-Z]{2,}(?=[A-Z][a-z])|[A-Z][a-z]/', ' $0', $fieldName)));
    }

    /**
     * @return array
     */
    protected function getFieldNameMapping()
    {
        return [];
    }
}
