<?php

namespace App\Http\Controllers\Api\Msg;

use App\Models\Admin\Admin;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\Home\User;
use App\Models\Log\LoginVerifyCode;
use App\Models\Student\StudentAccout;
use App\Models\Exam\ExamPlan;
use App\Models\Cost\StudentHourInfo;
use App\Models\Cost\CostExpense;
use App\Models\Packages\PackagesChargingItem;
use EasyWeChat\Factory;
use App\Models\Cost\CostArrearage;
use function EasyWeChat\Kernel\Support\generate_sign;
use App\Models\Packages\Packages;
use App\Models\Finance\Preferential;
use App\Models\Business\UserProduct;


class MsgController extends Controller
{
    protected $app = null;
    protected $config = null;
    
    public function __construct()
    {
        $config = [
            'app_id' => env('WECHAT_PAYMENT_APPID', ''),
            'mch_id' => env('WECHAT_PAYMENT_MCH_ID', ''),
            'key' => env('WECHAT_PAYMENT_KEY', ''),
            'cert_path' => env('WECHAT_PAYMENT_CERT_PATH', ''), //绝对
            'key_path' => env('WECHAT_PAYMENT_KEY_PATH', ''),
            'notify_url' => 'http://mini.com/api/small/payit/', // 支付通知地址
        ];
        $this->app = Factory::payment($config);
    }
    public function mobile_captcha()
    {
        try {
            $mobile = request('phone');
            $type = request('type')?request('type'):'login';
            //检索手机号
            if($type=='login'){
                $where = array(
                    'user_telphone'=>$mobile
                );
                $count = User::where($where)->count();
                $msg = "不存在此学员!";
            }else if($type=='coach'){
                $where = array(
                    'mobile_phone' =>$mobile,
                    'position' => '教练员'
                );
                $count = Admin::where($where)->count();
                $msg = "不存在此教练员!";
            }else{
                $count = 0;
                $msg = "数据异常!";
            }
            if(!$count){
                throw new \Exception($msg);
            }
            LoginVerifyCode::sendMobileCode($mobile,$type);
            return response()->json(['result' => 'success','code'=>200,'data' =>'']);
        } catch (\Exception $e) {
            return response()->json(['result' => 'fail','code'=>'405', 'msg' => $e->getMessage()]);
        }
    }

    public function getpayit()
    {
        $insert_id = request('id');
        $money = trim(request('money'));
        $user_id = request('userid');
        $openid = request('openid');
        $path = public_path();
        $config = [
            'app_id' => 'wx94373c89a4410b92',
            'mch_id' => '1517816761',
            'key' => 'krtsadgf4df1d5tg454fg1dr5g4rd5df',
            'cert_path' => $path.'/apiclient_cert.pem', //绝对
            'key_path' => $path.'/apiclient_key.pem',
            'notify_url' => 'https://miniapp.dev.krttech.com/api/small/payit/'.$insert_id.'/'.$user_id.'/'.$money, // 支付通知地址
            //'notify_url' => 'http://dev.backend.krttech.com/'.$insert_id.'/'.$user_id.'/'.$money, // 支付通知地址
        ];
        $app = Factory::payment($config);
        $result = $app->order->unify([
            'body' => 'test wx',
            'out_trade_no' => $insert_id,
            'total_fee' => $money,
            'notify_url' => '', // 支付结果
            'trade_type' => 'JSAPI',
            'openid' => $openid,
            
        ]);
        var_dump($result);
        
        $result['nonce_str'] = CostArrearage::getNonceStr();
        $result['prepay_id'] = $insert_id;
        var_dump($result);
        
        $wcPayParams = [
            "appId" => 'wx94373c89a4410b92',
            "timeStamp" => time(),
            "nonceStr" => $result['nonce_str'],
            "package" => "prepay_id=".$result['prepay_id'],
            "signType" => "MD5",
        ];$paySign=CostArrearage::MakeSign($wcPayParams);
        $wcPayParams['paySign']=$paySign;
        $wcPayParams['payId']=$insert_id;
        return response()->json($wcPayParams);
    }
    
    public function ispay()
    {
        $insert_id = request('id');
        $money = trim(request('money'));
        $user_id = request('userid');
        $openid = request('openid');
        if(empty($openid))
        {
        $mini = Factory::miniProgram();
        $result = $mini->auth->session($code);
        $openid = $result['openid'];
        }
        $path = public_path();
        $config = [
            'app_id' => 'wx94373c89a4410b92',
            'mch_id' => '1517816761',
            'key' => 'krtsadgf4df1d5tg454fg1dr5g4rd5df',
            'cert_path' => $path.'/apiclient_cert.pem', //绝对
            'key_path' => $path.'/apiclient_key.pem',
            'notify_url' => 'https://miniapp.dev.krttech.com/api/payit/'.$insert_id.'/'.$user_id.'/'.$money, // 支付通知地址
            //'notify_url' => 'http://dev.backend.krttech.com/'.$insert_id.'/'.$user_id.'/'.$money, // 支付通知地址
        ];
        
        $payment = Factory::payment($config); // 微信支付
        
        $result = $payment->order->unify([
            'body'         => '名称',
            'out_trade_no' => $insert_id,
            'trade_type'   => 'JSAPI',  // 
            'openid'       => $openid, // 
            'total_fee'    => 1, // 总价
        ]);
        
        // 如果成功生成统一下单的订单，那么进行二次签名
        if ($result['return_code'] === 'SUCCESS') {
            // 二次签名的参数必须与下面相同
            //$preid = isset($result['prepay_id'])?$result['prepay_id']:'';
            $params = [
                'appId'     => 'wx94373c89a4410b92',
                'timeStamp' => time(),
                'nonceStr'  => $result['nonce_str'],
                'package'   => 'prepay_id=' . $result['prepay_id'],
                'signType'  => 'MD5',
            ];
            
            // config('wechat.payment.default.key')为商户的key
            $params['paySign'] = generate_sign($params, 'krtsadgf4df1d5tg454fg1dr5g4rd5df');
            
            return response()->json($params);
            //return $params;
        } else {
            return response()->json($result);
            //return $result;
        }
    }
    
    public function paid(Request $request,$id,$uid,$mid)
    {

        //$openid = request('openid');
        $user_id = request('userid')?request('userid'):$request->route('uid');
        $money = trim(request('money'))?request('money'):$request->route('mid');
        $insert_id = $request->route('id');
        
        
        $path = public_path();
        $config = [
            'app_id' => 'wx94373c89a4410b92',
            'mch_id' => '1517816761',
            'key' => 'krtsadgf4df1d5tg454fg1dr5g4rd5df',
            'cert_path' => $path.'/apiclient_cert.pem', //绝对
            'key_path' => $path.'/apiclient_key.pem',
            'notify_url' => 'https://miniapp.dev.krttech.com/api/payit/'.$insert_id.'/'.$user_id.'/'.$money, // 支付通知地址
            //'notify_url' => 'http://dev.backend.krttech.com/'.$insert_id.'/'.$user_id.'/'.$money, // 支付通知地址
        ];
        $app = Factory::payment($config);
        
        $response = $app->handlePaidNotify(function($notify, $successful){
            // 使用通知里的 "微信支付订单号" 或者 "商户订单号" 去自己的数据库找到订单
            $apporder = $app->order->queryByOutTradeNumber($notify->out_trade_no);
            if (!$apporder) { // 如果订单不存在
                return 'Order not exist.'; // 告诉微信，我已经处理完了，订单没找到，别再通知我了
            }
            // 如果订单存在
            // 检查订单是否已经更新过支付状态
            $order = CostArrearage::find($notify->out_trade_no);
            if ($order->is_paid==3) { // 假设订单字段“支付时间”不为空代表已经支付
                return true; // 已经支付成功了就不再更新了
            }
            // 用户是否支付成功
            if ($successful) 
            {
                // 不是已经支付状态则修改为已经支付状态
                $order->is_paid = 3; // 更新支付时间为当前时间
                $order->status = 'paid';
            } else { // 用户支付失败
                //$order->status = 'paid_fail';
            }
            $order->save(); // 保存订单
            return true; // 返回处理完成
        });
            
            return $response;
    }
    
    public function arrears_charge(Request $request,$id,$uid,$mid)
    {
        $user_id = request('userid')?request('userid'):$request->route('uid');
        $money = trim(request('money'))?request('money'):$request->route('mid');
        $id = $request->route('id');
        
        $user = User::where('id',$user_id)->first();
        
        \DB::beginTransaction();
        try {
            
            if (!is_numeric($money)) {
                throw  new \Exception('交费金额格式不正确');
            }
            $income = CostArrearage::find($id);
            if (!$income) {
                throw  new \Exception('数据异常,不存在要交费的数据项！');
            }
            if ($money > $income->arrears_money) {
                throw  new \Exception('交费金额不能大于应缴金额！');
            } else {
                $last_arrears_money = $income->arrears_money - $money; //剩余欠费金额
            }
            
            
            $expense_type = $income->expense_type;
            if($expense_type==1){
                $msginfo = "课时费";
            }elseif ($expense_type==3){
                $msginfo = "考试费";
            }elseif ($expense_type==4){
                $msginfo = "报名费";
            }elseif ($expense_type==5){
                $msginfo = "学员充值费";
            }elseif ($expense_type==6){
                $msginfo = "其它费";
            }elseif ($expense_type==7){
                $msginfo = "课时违约费";
            }elseif ($expense_type==9){
                $msginfo = "套餐补缴费";
            }else{
                $msginfo = "其它费";
            }
            $paytype = 3;
            
            //优惠券
            $preferential = request('preferential_id') ? Preferential::find(request('preferential_id')) : null;
            //组装数据
            $rows['school_id'] = $user->school_id;//session("school_id");
            $rows['student_id'] = $income->student_id;
            $rows['arrearage_id'] = $id;
            $rows['expense_type'] = $income->expense_type;
            $rows['fee_money'] = $money; //应付金额
            $rows['pay_type'] = 3;
            $rows['remarks'] = '';
            if ($preferential) {
                //已经使用过优惠券的不可重复使用
                if ($preferential->money_limit > $income->fee_money) { //优惠券最低限额 大于 欠费总金额 则无法使用
                    throw  new \Exception('此优惠券无法使用：费用项目总金额没有小于优惠券最小限额！');
                }
                $quota = $preferential->quota;
                //判断是打折还是直减
                if($preferential->type=='PERCENT'){
                    $rows['paid_money'] = $rows['fee_money'] - ($rows['fee_money']*($quota/100)); //实付金额
                    $rows['preferential_money'] = $rows['fee_money']*($quota/100); //优惠金额 ： 暂未考虑折扣金额问题
                }else{
                    $rows['paid_money'] = $rows['fee_money'] - $quota; //实付金额
                    $rows['preferential_money'] = $quota; //优惠金额 ： 暂未考虑折扣金额问题
                }
                
                $map['preferential_detail'] = $preferential->name; //优惠券名称
                $map['preferential_money'] = $rows['preferential_money'] + $income->preferential_money; //优惠券金额
                $map['preferential_id'] = $preferential->id;
                
            } else {
                $rows['paid_money'] = $rows['fee_money']; //实付金额
                $rows['preferential_money'] = 0; //优惠金额
            }
            
            //             //判断是异常收费的情况添加
            //             if(request('charge_type') == 2){
            //                 $exception = \Config::get('paytype.PAYTYPE.18');
            //                 $rows['paid_money'] = 0; //实付金额
            //                 $rows['preferential_money'] = 0; //优惠金额
            //                 $rows['expense_type'] = $exception['id']; //异常收费状态
            //                 $msginfo = $exception['name'];
            //                 $map['expense_type'] = $exception['id'];
            //             }
            if ($paytype == 6) {//支付类型 = 学员账户余额
                if ($expense_type == 5) { //费用类型 = 学员充值 排除使用账户余额
                    throw  new \Exception('学员账户充值不能使用账户余额！');
                }
                //检测学员账户余额是否够本次支付
                $studentaccountinfo = StudentAccout::where(array('student_id' => $income->student_id))->first();
                
                $sumrefund = CostArrearage::findUserFee($income->student_id);
                $studentaccountinfo->balance = $studentaccountinfo->balance - $sumrefund;
                if ($studentaccountinfo->balance < $rows['paid_money']) { //账户余额小于 本次需要实际支付金额
                    throw  new \Exception('此学员账户余额不足！');
                }
                //扣除账户余额
                $mapaccount = array(
                    'balance' => $studentaccountinfo->balance - $rows['paid_money'],
                );
                $resultaccount = StudentAccout::where(array('id' => $studentaccountinfo->id))->update($mapaccount);
                if (!$resultaccount) {
                    throw  new \Exception('账户余额扣除失败！');
                }
            }
            //$admin = auth('admin')->user();
            $rows['deal_id'] = $user_id; //优惠金额
            if (CostExpense::costExpenseAdd($rows)) {
                $relation_id = $income->relation_id;
                $map['remarks'] = '';
                $map['paid_money'] = $rows['paid_money'] + $income->paid_money; //本次实付金额 + 历史实付金额
                $map['arrears_money'] = $last_arrears_money;
                if ($last_arrears_money > 0) {
                    $map['is_paid'] = 2;
                    //是否支付完成:1:未支付 2:进行中 3:已完成
                } else {
                    $map['is_paid'] = 3;
                }
                $info = CostArrearage::costArrearageUpdate($id, $map);
                if (!$info) {
                    throw  new \Exception('数据更新执行失败！');
                }
                if ($expense_type == 5) { //学员充值费用 需更新学员账户表
                    $studentaccount = StudentAccout::find($relation_id);
                    $studentaccount->amount = $studentaccount->amount + $money;
                    $studentaccount->balance = $studentaccount->balance + $money;
                    $result = $studentaccount->save();
                    if (!$result) {
                        throw  new \Exception('数据异常~收费失败！');
                    }
                } elseif ($expense_type == 3) { //学员考试费用 需要更新学员 exam_plan 表
                    $examplan = ExamPlan::find($relation_id);
                    $examplan->is_owed = 0;
                    $result = $examplan->save();
                    if (!$result) {
                        throw  new \Exception('数据异常~收费失败！');
                    }
                }
                
            } else {
                throw  new \Exception('数据插入执行失败！');
            }
            if($expense_type==4)
            {
                $pack = UserProduct::where('user_id',$user_id)->first();
                $packid = $pack->product_id;
                $charging_item_ids= (new PackagesChargingItem())->where("packages_id",$packid)->where('price','!=',0)->pluck("charging_item_id")->toarray();
                foreach($charging_item_ids as $k=>$v){
                    $packagesDetails[$k]['school_id'] = $user->school_id;// session("school_id");
                    $packagesDetails[$k]['student_id'] = $income->student_id;
                    $packagesDetails[$k]['pay_type'] = 3;
                    $packagesDetails[$k]['packages_id'] = $packid;
                    $packagesDetails[$k]['deal_id'] = $user_id;
                    $packagesDetails[$k]['chargin_item_id'] = $v;
                }
                (new \App\Models\Packages\PackagesDetails())->insert($packagesDetails);
            }
            
            $studentinfo = User::find($rows['student_id']);
            
            $user2 = StudentHourInfo::where('arrearage_id','=',$id)->first();
            if($user2)
            {
                $user2->is_paid = 3;
                $user2->updated_at = date('Y-m-d H:i:s');
                $user2->save();
            }
            
            
            //return response()->json(['result' => 'success', 'data' => $info]);
            
            $obj = CostArrearage::queryOrder($id);
            $reply = "<xml>
                    <return_code><![CDATA[SUCCESS]]></return_code>
                    <return_msg><![CDATA[OK]]></return_msg>
                </xml>";
            echo $reply;      // 向微信后台返回结果。
            \DB::commit();
            exit;
        } catch (\Exception $e) {
            \DB::rollBack();
            //return response()->json(['result' => 'fail', 'err_message' => $e->getMessage()]);
            $reply = "<xml>
                    <return_code><![CDATA[FAIL]]></return_code>
                    <return_msg><![CDATA[".$e->getMessage()."]]></return_msg>
                </xml>";
            echo $reply; 
        }
    }
    
    public function get_order()
    {
        
    }
    

}
