<?php

namespace Core\File;

use DvsaCommon\Utility\ArrayUtils;

class CsvFile implements FileInterface
{
    private $headers = null;
    private $rows = [];
    private $fileName;

    public function __construct()
    {
    }

    public function addRow(array $row)
    {
        $this->validateRow($row);
        $this->rows[] = $row;
    }

    public function addRows(array $rows)
    {
        foreach ($rows as $row) {
            $this->addRow($row);
        }
    }

    public function getRows()
    {
        return $this->rows;
    }

    private function validateRow(array $row)
    {
        $this->validateRowForInvalidValues($row);
        $this->validateRowLength($row);
    }

    private function validateRowLength(array $row)
    {
        if ($this->headers !== null || $this->getRowCount() > 0) {
            if ($this->getColumnCount() > 0) {
                if (count($row) != $this->getColumnCount()) {
                    throw new \InvalidArgumentException(
                        sprintf(
                            'Tried to add row to CSV files with a different (%s) number of columns than the rest of the file (%s)',
                            count($row),
                            $this->getColumnCount()
                        )
                    );
                }
            }
        }
    }

    public function setHeaders(array $headers)
    {
        $this->validateHeaders($headers);

        $this->headers = $headers;
    }

    private function validateHeaders(array $headers)
    {
        $this->validateRowForInvalidValues($headers);
        $this->validateHeadersLength($headers);
    }

    private function validateHeadersLength(array $headers)
    {
        if ($this->getRowCount() > 0) {
            if (count($headers) != $this->getColumnCount()) {
                throw new \InvalidArgumentException(
                    sprintf(
                        'Tried to set headers for CSV files with a different (%s) number of columns than the rest of the file (%s)',
                        count($headers),
                        $this->getColumnCount()
                    )
                );
            }
        }
    }

    private function validateRowForInvalidValues($row)
    {
        foreach ($row as $key => $rowValue) {
            if (!$this->isValidValue($rowValue)) {
                throw new \InvalidArgumentException(
                    sprintf("Unsupported value(%s) encountered under index: '%s'", gettype($row), $key)
                );
            }
        }
    }

    private function isValidValue($value)
    {
        return in_array(gettype($value), ['double', 'integer', 'boolean', 'string', 'NULL']);
    }

    public function getFileName()
    {
        return $this->fileName;
    }

    public function setFileName($fileName)
    {
        $this->fileName = $fileName;

        return $this;
    }

    public function getValue($rowNumber, $columnNumber)
    {
        if ($rowNumber >= $this->getRowCount()) {
            throw new \OutOfBoundsException('Unexisting cell in csv file');
        }

        if ($columnNumber >= $this->getColumnCount()) {
            throw new \OutOfBoundsException('Unexisting cell in csv file');
        }

        return $this->rows[$rowNumber][$columnNumber];
    }

    public function getHeader($columnNumber)
    {
        if ($columnNumber >= $this->getColumnCount()) {
            throw new \OutOfBoundsException('Header does not exits');
        }

        return $this->headers[$columnNumber];
    }

    public function getHeaders()
    {
        return $this->headers;
    }

    public function getSizeInBytes()
    {
        return strlen($this->getContent());
    }

    public function getContent()
    {
        $content = '';

        $allRows = $this->headers !== null
            ? array_merge([$this->headers], $this->rows)
            : $this->rows;

        $rowsAsStrings = ArrayUtils::map($allRows, function ($row) {
            $rowValuesAsStrings = ArrayUtils::map($row, function ($rowValue) {
                return $this->valueToString($rowValue);
            });

            return implode(',', $rowValuesAsStrings);
        });

        $content .= implode(PHP_EOL, $rowsAsStrings);

        return $content;
    }

    private function valueToString($value)
    {
        if (is_string($value)) {
            $value = str_replace('"', '""', $value);

            return '"'.$value.'"';
        } elseif (is_bool($value)) {
            return $value ? 'true' : 'false';
        } elseif (is_integer($value)) {
            return $value;
        } elseif (is_double($value)) {
            return $value;
        } elseif (is_null($value)) {
            return '';
        }

        throw new \InvalidArgumentException('Unsupported value encountered while generating CSV file: '.gettype($value));
    }

    public function getRowCount()
    {
        return count($this->rows);
    }

    public function getColumnCount()
    {
        if ($this->headers !== null) {
            return count($this->headers);
        } elseif ($this->getRowCount() > 0) {
            return count($this->rows[0]);
        } else {
            return 0;
        }
    }
}
