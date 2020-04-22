<?php
/**
 * Created by PhpStorm.
 * User: Will
 * Date: 23/09/2016
 * Time: 6:42 PM
 */

namespace App\Library;

use Illuminate\Support\Facades\Facade;

/**
 *
 * LaravelExcel Facade
 *
 * @category   Laravel Excel
 * @version    1.0.0
 * @package    maatwebsite/excel
 * @copyright  Copyright (c) 2013 - 2014 Maatwebsite (http://www.maatwebsite.nl)
 * @author     Maatwebsite <info@maatwebsite.nl>
 * @license    http://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt    LGPL
 */
class Excel extends Facade {

    /**
     * Return facade accessor
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'excel';
    }
}