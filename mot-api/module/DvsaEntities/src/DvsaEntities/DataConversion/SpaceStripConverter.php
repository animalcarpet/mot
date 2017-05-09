<?php

namespace DvsaEntities\DataConversion;

class SpaceStripConverter extends AbstractStringConverter
{
    protected $charMapping = [
        ' ' => '',
    ];
}
