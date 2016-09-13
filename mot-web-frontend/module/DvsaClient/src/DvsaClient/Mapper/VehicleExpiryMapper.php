<?php

namespace DvsaClient\Mapper;

use DvsaCommon\DtoSerialization\DtoReflectiveDeserializer;
use DvsaCommon\HttpRestJson\Client;
use DvsaCommon\Dto\Vehicle\VehicleExpiryDto;
use DvsaCommon\DtoSerialization\DtoReflectiveSerializer;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use DvsaCommon\UrlBuilder\VehicleUrlBuilder;

class VehicleExpiryMapper extends DtoMapper implements AutoWireableInterface
{

    /**
     * @var DtoReflectiveSerializer
     */
    private $reflectiveDeserializer;

    public function __construct(Client $client, DtoReflectiveDeserializer$reflectiveDeserializer)
    {
        parent::__construct($client);
        $this->reflectiveDeserializer = $reflectiveDeserializer;
    }

    /**
     * @param $vehicleId
     * @param bool $isDvlaVehicle
     * @return VehicleExpiryDto
     */
    public function getExpiryForVehicle($vehicleId, $isDvlaVehicle = false)
    {
        $vehicleExpiry = $this->get(VehicleUrlBuilder::testExpiryCheck($vehicleId, $isDvlaVehicle));
        return $this->reflectiveDeserializer->deserialize($vehicleExpiry['checkResult'], VehicleExpiryDto::class);
    }
}