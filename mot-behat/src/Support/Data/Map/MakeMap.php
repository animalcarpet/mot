<?php

namespace Dvsa\Mot\Behat\Support\Data\Map;

use Dvsa\Mot\Behat\Support\Data\Collection\DataCollection;
use Dvsa\Mot\Behat\Support\Data\Model\VehicleMakeDictionary;
use DvsaCommon\Dto\Vehicle\MakeDto;

class MakeMap
{
    private $collection;

    public function __construct()
    {
        $makeListDto = $this->createMakeListDto(VehicleMakeDictionary::get());
        $this->collection = new DataCollection(MakeDto::class, $makeListDto);

    }

    public function getByName($name)
    {
        $make = $this->collection->filter(function (MakeDto $make) use ($name){
            return $make->getName() === $name;
        });

        $this->validate($make);

        return $make->first();
    }

    private function validate(DataCollection $collection)
    {
        if ($collection->count() === 0) {
            throw new \InvalidArgumentException("Make not found");
        }
    }

    private function createMakeListDto(array $makeList)
    {
        $list = [];
        foreach ($makeList as $make) {
            $dto = new MakeDto();
            $dto->setId($make["id"]);
            $dto->setName($make["name"]);

            $list[$make["id"]] = $dto;
        }

        return $list;
    }
}
