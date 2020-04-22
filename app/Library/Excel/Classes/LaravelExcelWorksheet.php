<?php
/**
 * Created by PhpStorm.
 * User: Will
 * Date: 23/09/2016
 * Time: 6:21 PM
 */

namespace App\Library\Excel\Classes;

use Closure;

class LaravelExcelWorksheet extends \Maatwebsite\Excel\Classes\LaravelExcelWorksheet{

    /**
     * Manipulate a single row
     * @param  integer|callback|array $rowNumber
     * @param  array|callback         $callback
     * @return LaravelExcelWorksheet
     */
    public function row($rowNumber, $callback = null)
    {
        // If a callback is given, handle it with the cell writer
        if ($callback instanceof Closure)
        {
            $range = $this->rowToRange($rowNumber);

            return $this->cells($range, $callback);
        }

        // Else if the 2nd param was set, we will use it as a cell value
        if (is_array($callback))
        {
            // Interpret the callback as cell values
            $values = $callback;

            // Set start column
            $column = 'A';

            foreach ($values as $rowValue)
            {
                // Set cell coordinate
                $cell = $column . $rowNumber;

                // Set the cell value
                if (is_numeric($rowValue)) {
                    $this->setCellValueExplicit($cell, $rowValue);
                } else {
                    $this->setCellValue($cell, $rowValue);
                }
                $column++;
            }
        }

        // Remember that we have added rows
        $this->hasRowsAdded = true;

        return $this;
    }
}