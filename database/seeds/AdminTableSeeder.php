<?php
/**
 * Created by PhpStorm.
 * User: Will
 * Date: 5/30/16
 * Time: 3:11 PM
 */

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class AdminTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('admin')->insert([
            'admin_name' => 'admin',
            'password' => md5(md5('password').'salta'),
            'salt'=>'salta'
        ]);

        DB::table('roles')->insert([
            'name' => 'admin',
            'display_name' => 'Super Admin',
            'description' => ''
        ]);
    }
}
