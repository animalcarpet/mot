<?php

namespace DvsaCommon\DtoSerialization;

use DvsaCommon\Date\Time;
use DvsaCommon\Date\TimeSpan;
use DvsaCommon\DtoSerialization\Convertion\DtoDateTimeConverter;
use DvsaCommon\DtoSerialization\Convertion\DtoTimeSpanConverter;
use DvsaCommon\Utility\ArrayUtils;

class DtoConvertibleTypesRegistry
{
    private $converters;

    public function __construct()
    {
        $this->registerConverters();
    }

    private function registerConverters()
    {
        $this->register(\DateTime::class, new DtoDateTimeConverter());
        $this->register(TimeSpan::class, new DtoTimeSpanConverter());

        $this->register(Time::class, new CallbackDtoConverter(
            function ($json) {
                return Time::fromIso8601($json);
            },
            function (Time $time) {
                return $time->toIso8601();
            }));
    }

    public function getConvertibleTypes()
    {
        return array_keys($this->converters);
    }

    public function isConvertibleType($class)
    {
        return in_array($class, $this->getConvertibleTypes());
    }

    /**
     * @param $class
     * @return DtoConverterInterface
     */
    public function getConverter($class)
    {
        $converter = ArrayUtils::tryGet($this->converters, $class);

        if (!$converter) {
            throw new \InvalidArgumentException('Converter for \'' . $class . '\' has not been registered.');
        }

        return $converter;
    }

    public function register($class, DtoConverterInterface $converter)
    {
        if (is_subclass_of($class, ReflectiveDtoInterface::class)) {
            throw DtoReflectionException::createCannotRegisterDtoAsConvertible($class);
        }

        $this->converters[$class] = $converter;
    }
}
