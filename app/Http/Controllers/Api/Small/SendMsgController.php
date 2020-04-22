<?php

namespace App\Http\Controllers\Api\Small;

use App\Models\Admin\Admin;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\Home\User;
use PhpSms;

class SendMsgController extends Controller
{
    //

    //发送提醒邮件
    public function sendmsginfo(){

        $info = json_decode(request('sendinfo'));
        if(!$info['phone']){
            return $info;
        }
        $templates = [
            'Aliyun' => $info['phone'],
        ];
        $content = "";
        if($info['data']){
            $classinfo = implode(",",$info['data']);
        }else{
            $classinfo = "";
        }

        $sendinfo = array(
            'class' => $classinfo,
            'name' => '乔增浩',
            'vehicle' => '[贵3698学]',
            'product'=>'dsd'
        );
        $mobile = "15801195012";
        PhpSms::make()
            ->to($info['phone'])
            ->template($templates)
            ->data($sendinfo)
            ->content($content)
            ->send();

    }


}
