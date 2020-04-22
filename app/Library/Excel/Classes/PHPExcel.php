<?php
/**
 * Created by PhpStorm.
 * User: Will
 * Date: 23/09/2016
 * Time: 6:30 PM
 */

namespace App\Library\Excel\Classes;


class PHPExcel extends \Maatwebsite\Excel\Classes\PHPExcel {


    /**
     * Create sheet and add it to this workbook
     *
     * @param  int|null   $iSheetIndex Index where sheet should go (0,1,..., or null for last)
     * @param bool|string $title
     * @throws \PHPExcel_Exception
     * @return LaravelExcelWorksheet
     */
    public function createSheet($iSheetIndex = null, $title = false)
    {
        // Init new Laravel Excel worksheet
        $newSheet = new LaravelExcelWorksheet($this, $title);

        // Add the sheet
        $this->addSheet($newSheet, $iSheetIndex);

        // Return the sheet
        return $newSheet;
    }
}