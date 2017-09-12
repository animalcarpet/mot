<?php

namespace Site\Csv\TQI;

class Csv
{
    private $matrix = [];

    public function __construct(int $rowsNumber, int $columnsNumber)
    {
        $matrix = [];
        for ($rowIndex=0; $rowIndex<$rowsNumber; $rowIndex++) {
            $matrix[$rowIndex] = [];
            for($columnIndex=0; $columnIndex<$columnsNumber; $columnIndex++) {
                $matrix[$rowIndex][] = "";
            }
        }

        $this->matrix = $matrix;
    }

    public function addField(int $row, int $column, string $value = null): Csv
    {
        $this->validate($row, $column);

        $this->matrix[$row][$column] = $value;

        return $this;
    }

    public function getField(int $row, $column): string {
        $this->validate($row, $column);

        return $this->matrix[$row][$column];
    }

    public function toArray()
    {
        return $this->matrix;
    }

    private function validate(int $row, int $column)
    {
        if (array_key_exists($row, $this->matrix) === false) {
            throw new \InvalidArgumentException(sprintf("Row Index '%d' Out Of Bounds", $row));
        }

        if (array_key_exists($column, $this->matrix[$row]) === false) {
            throw new \InvalidArgumentException(sprintf("Column Index '%d' Out Of Bounds", $column));
        }
    }
}
