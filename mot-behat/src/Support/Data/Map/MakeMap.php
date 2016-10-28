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

    /**
     * @param $code
     * @return MakeDto
     */
    public function getByCode($code)
    {
        return $this->collection->get($code);
    }

    public function getByName($name)
    {
        $make = $this->collection->filter(function (MakeDto $make) use ($name){
            return $make->getName() === $name;
        });

        $this->validate($make);

        $make->first();
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
            $dto->setCode($make["code"]);

            $list[$make["code"]] = $dto;
        }

        return $list;
    }
}
