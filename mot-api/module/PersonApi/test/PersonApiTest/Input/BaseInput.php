<?php

namespace PersonApiTest\Input;

class BaseInput extends \PHPUnit_Framework_TestCase
{
    protected function createString($length, $char = 'X')
    {
        return str_repeat($char, $length);
    }

    protected function tooLongMsg($subject, $maxLength)
    {
        return str_replace('%max%', $maxLength, $subject);
    }
}
