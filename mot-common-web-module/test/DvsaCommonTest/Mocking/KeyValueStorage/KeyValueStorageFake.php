<?php

namespace DvsaCommonTest\Mocking\KeyValueStorage;

use DvsaCommon\KeyValueStorage\ArrayKeyValueStorage;

class KeyValueStorageFake extends ArrayKeyValueStorage
{
    public function clear()
    {
        $this->storedData = [];
    }
}
