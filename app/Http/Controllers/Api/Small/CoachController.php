<?php

namespace App\Http\Controllers\Api\Small;

use App\Models\Admin\CoachReview;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

class CoachController extends BaseController
{
    private $_school_id;
    private $_user_id;
    private $_packages_id;
    private $_recedata;
    private $_username;

    /*
     * @Des：析构函数
     * */
    public function __construct()
    {
        parent::__construct();
        $this->_recedata = request()->all();
        $parms = array(
            'openid' => $this->_openid,
            'token' => $this->_token,
            'user_id' => $this->_userid //session('user_id')
        );
        if (!$this->checkIsLogin($parms)) {
            echo $this->notLoginInfo();
            exit;
        }
        //接收参数检测
        $info = $this->checkParms($this->_recedata);
        if ($info) {
            echo $this->notPassParms($info);
            exit;
        }
        $this->_packages_id = session('packages_id');
        $this->_user_id = session('user_id');
        $this->_school_id = session('school_id');
        $this->_username = session('username');
    }
    //
    public function getcoachlist(){
        $options = [
            'user_truename' => request('user_truename'),
            'admin_name' => request('admin_name'),
            'start_date' => request('start_date'),
            'finish_date' => request('finish_date'),
        ];
        $coach = \Config::get('evaluation.COACH');
        $list = CoachReview::GetCoachEvaluation(options_filter($options),1)->paginate(10);
        foreach ($list as $v){
            $v->remarklist = "";
            if($v->remark){
                $tmp = explode(",",$v->remark);
                for($i=0;$i<count($tmp);$i++){
                    $tmplist[$i] = $coach[$tmp[$i]];
                }
                $v->remarklist = json_encode($tmplist);
                unset($tmplist);
                unset($tmp);
            }
        }
        print_r($list->toArray());

    }

}
