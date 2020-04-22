<?php
/**
 * Created by PhpStorm.
 * User: Will
 * Date: 23/09/2016
 * Time: 6:01 PM
 */

namespace App\Library\Excel;

use App\Library\Excel\Classes\PHPExcel;
use Maatwebsite\Excel\Readers\LaravelExcelReader;
use Maatwebsite\Excel\Writers\LaravelExcelWriter;
use Maatwebsite\Excel\Exceptions\LaravelExcelException;

class Excel extends \Maatwebsite\Excel\Excel {
    public function __construct(PHPExcel $excel, LaravelExcelReader $reader, LaravelExcelWriter $writer)
    {
        // Set Excel dependencies
        $this->excel = $excel;
        $this->reader = $reader;
        $this->writer = $writer;
    }
}
?>
