<?php

namespace TestSupport\Helper;

/**
 * Generates usernames, email addresses etc. Designed to be instantiated once per data-creation request.
 */
class DataGeneratorHelper
{
    const NUMBER_FORMAT = 'AE%05d';

    /**
     * A number to help distinguish generated entities
     *
     * @var int
     */
    private $differentiator;

    private function __construct()
    {
        $this->differentiator = uniqid("", true);
    }

    /**
     * @param $data array containing optional 'diff' element of a string differentiator
     *
     * @return DataGeneratorHelper
     */
    public static function buildForDifferentiator($data)
    {
        $dataGenSupport = new self();
        if (isset($data['diff'])) {
            $dataGenSupport->differentiator = $data['diff'];
        }

        return $dataGenSupport;
    }

    public function generateAeRef(){
        return sprintf(self::NUMBER_FORMAT, rand(10, 10000));
    }

    public function addressLine1()
    {
        return substr('Flat ' . $this->differentiator . ' Lord House', 0, 45);
    }

    public function addressLine2()
    {
        return substr('Apartment ' . $this->differentiator, 0 , 45);
    }

    public function emailAddress()
    {
        return $this->differentiator . '@example.com';
    }

    public function startDate()
    {
        return date('Y-m-d H:i:s', strtotime('-6 months'));
    }

    public function organisationName()
    {
        return 'Test Organisation ' . $this->differentiator;
    }

    public function siteName()
    {
        return 'Test Site ' . $this->differentiator;
    }

    public function firstName()
    {
        return substr($this->differentiator . 'FakeTest', 0, 12);
    }

    public function surname()
    {
        return substr('Surname-Mc' . $this->differentiator, 0, 12);
    }

    public function middleName()
    {
        return substr('Middle-Mc' . $this->differentiator, 0, 7);
    }

    public function drivingLicenceNumber()
    {
        return 'D' . $this->differentiator;
    }

    public function drivingLicenceRegion()
    {
        return 'GB';
    }

    public function phoneNumber()
    {
        return '01-' . rand(1000000000, 9999999999);
    }

    public function username()
    {
        return substr('User' . $this->generateRandomString(15) . $this->differentiator, 0, 25);
    }

    public function prefix($role)
    {
        return (is_null($role)) ? 'user-' : strtolower($role) . '-';
    }

    public function generateRandomString($length = 5) {
        return substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, $length);
    }
}
