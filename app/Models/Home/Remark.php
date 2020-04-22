<?php

namespace App\Models\Home;

use App\Models\Admin\Admin;
use App\Models\BaseModel;

/**
 * Class Comment
 * @package App\Models\Home
 * @property $user_id
 * @property $handle_admin_id
 * @property $content
 * @property-read \App\Models\Admin\Admin $handle_admin
 * @property-read \App\Models\Home\User $user
 */
class Remark extends BaseModel
{
    protected $table = 'user_remark';
    protected $rules = [
        'user_id' => 'required|int|min:0',
        'handle_admin_id' => 'required|int|min:0',
        'content' => 'required|string|min:1'
    ];

    protected $customAttributes = [
        'user_id' => '用户',
        'handle_admin_id' => '评注人',
        'content' => '内容'
    ];

    public function user(){return $this->belongsTo(User::class);}
    public function handle_admin(){return $this->belongsTo(Admin::class);}
}
