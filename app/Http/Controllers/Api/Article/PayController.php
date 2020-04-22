<?php namespace App\Http\Controllers\Admin;

/**
 * Created by PhpStorm.
 * User: Will
 * Date: 5/31/16
 * Time: 11:12 AM
 */

//use App\Library\Log;
use View, Session, Validator;
use App\Models\Admin\Admin;
use Illuminate\Foundation\Auth\ThrottlesLogins;
use Illuminate\Foundation\Auth\AuthenticatesAndRegistersUsers;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Log;
use Illuminate\Http\Request;
use App\Models\Cost\CostArrearage;
use App\Models\Student\StudentAccout;
use App\Models\Exam\ExamPlan;
use App\Models\Cost\StudentHourInfo;
use App\Models\Cost\CostExpense;
use App\Models\Packages\PackagesChargingItem;

class PayController extends Controller
{
    use AuthenticatesAndRegistersUsers, ThrottlesLogins;

    protected $guard = 'admin';
    protected $redirectTo = '/admin';
    
    protected $config = [
        'app_id' => '2016092000555178',
        'notify_url' => 'http://saas.com/admin/notify/',
        'return_url' => 'http://saas.com/admin/return/',
        'ali_public_key' => 'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAuUIXlO52vSlq8jxZPkJF4xNFmtksWEAE/0EPWUddiDoW940Vy8JzAZTyf93Hagc2HsxzwRWeO8Sj/+CN+QlNedvWc7rdMP+M8cGHKJuZhYzOKurcXlUUnbvex79B75Nu/bsRPfwpLyceVw+Kj+NA3mMQYARpLKWZS2TWZ6b2oTscgyt3f0zoev4ZQBF1lTeCc6Rlpik+gUQLuW5Z+ZKsVIXCkrMLedQeaFCtbaPQ8/Y3kArx0TF73wvYm2Qy+cRN7BCf2zQbuk/5KTOqfanlI4HVCht/iicF1fd8/t4CahueI4IA+M/MM/adrWHYuxVHgTNf8Ht9aPO4zEGtnUVABQIDAQAB',
        // 加密方式： **RSA2**
        'private_key' => 'MIIEvgIBADANBgkqhkiG9w0BAQEFAASCBKgwggSkAgEAAoIBAQC5QheU7na9KWryPFk+QkXjE0Wa2SxYQAT/QQ9ZR12IOhb3jRXLwnMBlPJ/3cdqBzYezHPBFZ47xKP/4I35CU1529Zzut0w/4zxwYcom5mFjM4q6txeVRSdu97Hv0Hvk279uxE9/CkvJx5XD4qP40DeYxBgBGkspZlLZNZnpvahOxyDK3d/TOh6/hlAEXWVN4JzpGWmKT6BRAu5bln5kqxUhcKSswt51B5oUK1to9Dz9jeQCvHRMXvfC9ibZDL5xE3sEJ/bNBu6T/kpM6p9qeUjgdUKG3+KJwXV93z+3gJqG54jggD4z8wz9p2tYdi7FUeBM1/we31o87jMQa2dRUAFAgMBAAECggEARuU8EQqQ9iL7gmgF3wWNqTCe2ntxtPQK9YP4U7oz2QYh8+pSBQAM8vYFN1mwDqtj0rV7NtEHFOhTuoA81KjytUej8fX4399sGLhu+ONTBQC1hUcLvi3hDdvvjZFrsjtBpmgIWSg+uoTF35ta82WMVY6jnZsShLt6xpd4VYJXr6tAC6wmiECHZmqm/78IWCgs6LLjTO6ZyNsGJI0E0aIWLPjwLW7IP7osTg2sH9D190gATDPdxlfycFo34E+GH6ZXEHAOMqxCvzwKO8dTDKqn7gl2FjsrYXnav4Z0HdAoB/iabQ76dI55VVY7wfobHm5SSh+6CJR+l8A8OVzXfSG+AQKBgQDuOrJ/2QzJtHzv9juVgOG5aJcf/DFBxo6oDGZf6Pe3pqkXLTrASpNvO8fF237qAeNI2jWJXYaj7EjRk6lPfqTsdcUqqdspFUSqC2CLhrIf0wWYJwFP5oe/YVc03zzYm2idT8cRo2uN64b+MSk5sIkeaEeaQlZ6hfrZwDbvmfH2dQKBgQDHE9dsiith6NylOMnzxHe0IS700tw8gqRAUnB8Z+jcMO7ok0qBv2/samlxmYXvb4W/trB6ZOkPOmkecL/N2Y5eNU2tLsp60NNbnLTWaTtWVUn5xE3cDg058pz6bC6JWMcRGdxWmIFqv3+y1hFKmcMcBwRkA2lJUJHw1RMk9uaRUQKBgH6SaSxMIeM5JfhIlzfDlipwS3sO/wy3diwLComCubq1rblGqqRn+xVqaaXSDQg/oIagiTlGm7aUX8wZ7Cb0XqPTnsIgJPUa+7Rs/wqishj+gUZp0uSk0xL3oOHSif179IVAUcApV4e7z6lWbVTdWrzxIrnu7QuEC9eNrKJ45HnRAoGBALZ+u+bmKqrQuyRDEO8EVe+s08zfV8GoyUWlzTReVRs1SG2wSIb0pXeGfS8EW8GI7IU4xkMNKpEpLKAZH9tm6pn2J0TxNfCsanT7DNPZF+omuW/bwrxNrVZH0BvI/EgwOBy3JkPD/i+LaVbZ4nQOzMtuq1m7vtLUp1StH6WtKORhAoGBAKmJQCkoWHkcZpGMvHnYw/fjT4HRlnkYHZ7S848OnljD78TE3Bhl/qdGROgs8yAuSsNq19zCzkwuhJzqi3WcrwbW+xb1F7ag66ngw5rUTfcMEn8QAmL6EV3DMubW0awqCaYK4IGp215VAmQduUHxEo95win771fCjnd/o4PxftuC',
        'log' => [ // optional
            'file' => './logs/alipay.log',
            'level' => 'info', // 建议生产环境等级调整为 info，开发环境为 debug
            'type' => 'single', // optional, 可选 daily.
            'max_file' => 30, // optional, 当 type 为 daily 时有效，默认 30 天
        ],
        'http' => [ // optional
            'timeout' => 60.0,
            'connect_timeout' => 5.0,
            // 更多配置项请参考 [Guzzle](https://guzzle-cn.readthedocs.io/zh_CN/latest/request-options.html)
        ],
        'mode' => 'dev', // optional,设置此参数，将进入沙箱模式
    ];
    
    public function index()
    {
        $id = request('id');
        $amount = request('fee');
        $msg = request('subject');
        $uid = request('uid');

        
        $order = [
            'out_trade_no' => $id,
            'total_amount' => $amount,
            'subject' => '学员交费 - 测试',
        ];
        
        $this->config['return_url'] = 'http://saas.com/admin/return/'.$id.'/'.$uid.'/'.$amount;
        
        $alipay = Pay::alipay($this->config)->web($order);
        
        return $alipay->send();// laravel 框架中请直接 `return $alipay`

    }
    
    public function return()
    {
        $data = Pay::alipay($this->config)->verify(); // 是的，验签就这么简单！
        
        // 订单号：$data->out_trade_no
        // 支付宝交易号：$data->trade_no
        // 订单总金额：$data->total_amount
        $error = '成功';
        return view('admin.success')->with(get_defined_vars());
    }
    
    public function notify()
    {
        $alipay = Pay::alipay($this->config);
        
        try{
            $data = $alipay->verify(); // 是的，验签就这么简单！
            
            // 请自行对 trade_status 进行判断及其它逻辑进行判断，在支付宝的业务通知中，只有交易通知状态为 TRADE_SUCCESS 或 TRADE_FINISHED 时，支付宝才会认定为买家付款成功。
            // 1、商户需要验证该通知数据中的out_trade_no是否为商户系统中创建的订单号；
            // 2、判断total_amount是否确实为该订单的实际金额（即商户订单创建时的金额）；
            // 3、校验通知中的seller_id（或者seller_email) 是否为out_trade_no这笔单据的对应的操作方（有的时候，一个商户可能有多个seller_id/seller_email）；
            // 4、验证app_id是否为该商户本身。
            // 5、其它业务逻辑情况
            
            Log::debug('Alipay notify', $data->all());
        } catch (Exception $e) {
            // $e->getMessage();
        }
        
        return $alipay->success()->send();// laravel 框架中请直接 `return $alipay->success()`
    }
    
    public function arrears_charge(Request $request,$id,$uid,$mid)
    {
        $user_id = request('userid')?request('userid'):$request->route('uid');
        $money = trim(request('money'))?request('money'):$request->route('mid');
        $id = $request->route('id');
        
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
            $rows['school_id'] = session("school_id");
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
                    $packagesDetails[$k]['school_id'] = session("school_id");
                    $packagesDetails[$k]['student_id'] = $income->student_id;
                    $packagesDetails[$k]['pay_type'] = 3;
                    $packagesDetails[$k]['packages_id'] = $packid;
                    $packagesDetails[$k]['deal_id'] = $user_id;
                    $packagesDetails[$k]['chargin_item_id'] = $v;
                }
                (new \App\Models\Packages\PackagesDetails())->insert($packagesDetails);
            }
            
            $studentinfo = User::find($rows['student_id']);
            
            $user = StudentHourInfo::where('arrearage_id','=',$id)->first();
            if($user)
            {
                $user->is_paid = 3;
                $user->updated_at = date('Y-m-d H:i:s');
                $user->save();
            }
            
            
            //return response()->json(['result' => 'success', 'data' => $info]);
            
            $obj = CostArrearage::getNotifyData();
            if ($obj)
            {
                file_put_contents('notify.txt', '订单号：' . $obj->out_trade_no . "\r\n", FILE_APPEND);
                file_put_contents('notify.txt', '订单金额：' .$obj->total_fee . "\r\n\r\n", FILE_APPEND);
                $data = array(
                    'appid'                =>    $obj->appid,
                    'mch_id'            =>    $obj->mch_id,
                    'nonce_str'            =>    $obj->nonce_str,
                    'result_code'        =>    $obj->result_code,
                    'openid'            =>    $obj->openid,
                    'trade_type'        =>    $obj->trade_type,
                    'bank_type'            =>    $obj->bank_type,
                    'total_fee'            =>    $obj->total_fee,
                    'cash_fee'            =>    $obj->cash_fee,
                    'transaction_id'    =>    $obj->transaction_id,
                    'out_trade_no'        =>    $obj->out_trade_no,
                    'time_end'            =>    $obj->time_end
                );
                // 拼装数据进行第三次签名
                $reply = "<xml>
                    <return_code><![CDATA[SUCCESS]]></return_code>
                    <return_msg><![CDATA[OK]]></return_msg>
                </xml>";
                echo $reply;      // 向微信后台返回结果。
                \DB::commit();
                exit;
                
            }
            else
            {
                throw new \Exception('FAIL');
            }
        } catch (\Exception $e) {
            \DB::rollBack();
            return response()->json(['result' => 'fail', 'err_message' => $e->getMessage()]);

        }
    }


 
}
