<?php

namespace CoreTest\File;

use Core\File\CsvFile;
use DvsaCommon\Guid\Guid;

class CsvFileTest extends \PHPUnit_Framework_TestCase
{
    public function test_getRowCount_returnNumberOfRows()
    {
        // GIVEN I have a CVS file with 3 rows
        $csv = new CsvFile();
        $csv->addRow([1, 2]);
        $csv->addRow([3, 4]);
        $csv->addRow([5, 6]);

        // WHEN I ask for number of rows
        $count = $csv->getRowCount();

        // THEN i get a correct value
        $this->assertEquals(3, $count);
    }

    public function test_getColumnCount_returnsColumnCountOfHeaderWhenItExists()
    {
        // GIVEN I have a CVS file with two headers
        $csv = new CsvFile();
        $csv->setHeaders(['Header1', 'Header2']);

        // WHEN I ask for number of columns
        $count = $csv->getColumnCount();

        // THEN i get a correct value
        $this->assertEquals(2, $count);
    }

    public function test_getColumnCount_returnsColumnCountOfFirstRowWhenNoHeaderIsGiven()
    {
        // GIVEN I have a CVS file with a row with two columns
        $csv = new CsvFile();
        $csv->addRow(['Value1', 'Value2']);

        // WHEN I ask for number of columns
        $count = $csv->getColumnCount();

        // THEN i get a correct value
        $this->assertEquals(2, $count);
    }

    public function test_getColumnCount_returnsZeroIfFileIsEmpty()
    {
        // GIVEN I have an empty CVS file
        $csv = new CsvFile();

        // WHEN I ask for number of columns
        $count = $csv->getColumnCount();

        // THEN i get a correct value
        $this->assertEquals(0, $count);
    }

    public function test_getCell_returnsCorrectValue()
    {
        $expectedValue = 6;

        // GIVEN I have a CVS file with few values
        $csv = new CsvFile();
        $csv->addRow([1, 2]);
        $csv->addRow([3, 4]);
        $csv->addRow([5, $expectedValue]);

        // WHEN I query for a specific cell value
        $actualValue = $csv->getValue(2, 1);

        // THEN I get the cell has correct value
        $this->assertEquals($expectedValue, $actualValue);
    }

    /**
     * @expectedException \OutOfBoundsException
     */
    public function test_getCell_throwsExceptionWhenRequestedCellIsOutOfBoundsOfRows()
    {
        // GIVEN I have a CVS file with few values
        $csv = new CsvFile();
        $csv->addRow([1, 2]);
        $csv->addRow([3, 4]);

        // WHEN I query for a value outside of bounds of the number of rows
        $csv->getValue(5, 1);

        // THEN an exception is thrown
    }

    /**
     * @expectedException \OutOfBoundsException
     */
    public function test_getCell_throwsExceptionWhenRequestedCellIsOutOfBoundsOfColumns()
    {
        // GIVEN I have a CVS file with few values
        $csv = new CsvFile();
        $csv->addRow([1, 2]);
        $csv->addRow([3, 4]);

        // WHEN I query for a value outside of bounds of the number of rows
        $csv->getValue(1, 5);

        // THEN an exception is thrown
    }

    public function test_getContent_handlesFilesWithNoHeaders()
    {
        // GIVEN I have a CVS file with only values (no headers)
        $csv = new CsvFile();

        $csv->addRow(['Value1', 'Value2']);
        $csv->addRow(['Value3', 'Value4']);
        // WHEN I generate it's content
        $content = $csv->getContent();

        // THEN I get file content with values separated by delimiter
        $this->assertEquals("\"Value1\",\"Value2\"\n\"Value3\",\"Value4\"", $content);
    }

    public function test_getContent_handlesFilesWithHeaders()
    {
        // GIVEN I have a CVS file with headers
        $csv = new CsvFile();
        $csv->setHeaders(['Header1', 'Header2']);

        // WHEN I generate it's content
        $content = $csv->getContent();

        // THEN I get headers first
        $this->assertEquals('"Header1","Header2"', $content);
    }

    public function test_getContent_surroundsStringsWithQuotationMarks()
    {
        // GIVEN I have a CVS file with a string value
        $csv = new CsvFile();
        $csv->addRow(['Lorem ipsum']);

        // WHEN I transform it into content
        $content = $csv->getContent();

        // THEN the value is surrounded with quotation marks
        $this->assertEquals('"Lorem ipsum"', $content);
    }

    public function test_getContent_escapesQuotationMarksWithQuotationMarks()
    {
        // GIVEN I have a CVS file with a string value with quotation marks inside
        $csv = new CsvFile();
        $csv->addRow(['The "Game of Thrones" is a comedy TV series.']);

        // WHEN I transform it into content
        $content = $csv->getContent();

        // THEN the quotation marks are preceded with quotation marks
        $this->assertEquals('"The ""Game of Thrones"" is a comedy TV series."', $content);
    }

    public function test_getContent_printsBooleanCorrectly()
    {
        // GIVEN I have a CVS file with boolean values
        $csv = new CsvFile();
        $csv->addRow([false, true]);

        // WHEN I transform it into content
        $content = $csv->getContent();

        // THEN the values are printed as plain unquoted text
        $this->assertEquals('false,true', $content);
    }

    public function test_getContent_printsIntegersCorrectly()
    {
        // GIVEN I have a CVS file with integer values
        $csv = new CsvFile();
        $csv->addRow([1, 5]);

        // WHEN I transform it into content
        $content = $csv->getContent();

        // THEN the values are printed as plain unquoted text
        $this->assertEquals('1,5', $content);
    }

    public function test_getContent_printsDoubleCorrectly()
    {
        // GIVEN I have a CVS file with double values
        $csv = new CsvFile();
        $csv->addRow([10.05, 5.663]);

        // WHEN I transform it into content
        $content = $csv->getContent();

        // THEN the values are printed as plain unquoted text
        $this->assertEquals('10.05,5.663', $content);
    }

    public function test_getContent_printsNullAsAnEmptyCell()
    {
        // GIVEN I have a CVS file with null values
        $csv = new CsvFile();
        $csv->addRow([null, null]);

        // WHEN I transform it into content
        $content = $csv->getContent();

        // THEN the values are printed as empty text
        $this->assertEquals(',', $content);
    }

    public function test_getSizeInBytes_calculatesFileSize()
    {
        // GIVEN I have a CVS file with some values
        $csv = new CsvFile();
        $csv->setHeaders(['Header1']);
        $csv->addRow(['Value1']);

        // WHEN I get it's file size
        $fileSize = $csv->getSizeInBytes();

        // THEN the values are printed as plain unquoted text
        $this->assertEquals(strlen('"Header1","Value1"'), $fileSize);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function test_setHeaders_throwsExceptionWhenNumberOfColumnsDoesNotMatchFirstRow()
    {
        // GIVEN I have a file with 3 colums in first row
        $csv = new CsvFile();
        $csv->addRow([1, 2, 3]);

        // WHEN I set rows with a different number of columns
        $csv->setHeaders([1, 2, 3, 4]);

        // THEN an exception is thrown
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function test_setHeaders_throwsExceptionGivenUnsupportedValues()
    {
        // GIVEN I'm I have a csv file
        $csv = new CsvFile();

        // WHEN I try to set an invalid value (e.g. object) as a header
        $csv->setHeaders(['string', 1, 0.9, new Guid()]);

        // THEN an exception is thrown
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function test_addRow_throwsExceptionWhenNumberOfColumnsDoesNotMatchWithHeader()
    {
        // GIVEN I have a CSV file with 3 column headers
        $csv = new CsvFile();
        $csv->setHeaders([1, 2, 3]);

        // WHEN I try to add a row with different number of columns
        $csv->addRow([1, 2, 3, 4]);

        // THEN an exception is thrown
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function test_addRow_throwsExceptionWhenNumberOfColumnsDoesNotMatchWithFirstRow()
    {
        // GIVEN I have a CSV file with 3 column row
        $csv = new CsvFile();
        $csv->addRow([1, 2, 3]);

        // WHEN I try to add a row with different number of columns
        $csv->addRow([1, 2, 3, 4]);

        // THEN an exception is thrown
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function test_addRow_throwsExceptionWhenGivenUnsupportedValues()
    {
        // GIVEN I'm I have a csv file
        $csv = new CsvFile();

        // WHEN I try to add row with invalid value (e.g. object)
        $csv->addRow(['string', 1, 0.9, new Guid()]);

        // THEN an exception is thrown
    }

    public function test_getHeader_returnSpecificHeader()
    {
        // GIVEN I have a CSV file with headers
        $csv = new CsvFile();
        $csv->setHeaders(['A', 'B', 'C']);

        // WHEN I request a header
        $header = $csv->getHeader(1);

        // THEN I am given it
        $this->assertEquals('B', $header);
    }

    /**
     * @expectedException \OutOfBoundsException
     */
    public function test_getHeader_throwsExceptionWhenNoneExists()
    {
        // GIVEN I have a CSV file without any headers
        $csv = new CsvFile();

        // WHEN I request a header that doesn't exists
        $csv->getHeader(0);

        // THEN an exception is thrown
    }

    /**
     * @expectedException \OutOfBoundsException
     */
    public function test_getHeader_throwsExceptionWhenItsOutOfBounds()
    {
        // GIVEN I have a CSV file with headers
        $csv = new CsvFile();
        $csv->setHeaders(['A', 'B', 'C']);

        // WHEN I request a header that doesn't exists
        $csv->getHeader(4);

        // THEN an exception is thrown
    }
}
