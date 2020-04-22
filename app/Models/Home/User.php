<?php
namespace App\Models\Home;

use App\Library\Loglc\Word;
use App\Models\AddError;
use App\Models\Admin\Admin;
use App\Models\Admin\Notification;
use App\Models\Appointment\Appointment;
use App\Models\Appointment\Comment;
use App\Models\Data\PayType;
use App\Models\Data\School;
use App\Models\Finance\Income;
//use App\Models\Business\Product;
use App\Models\Business\UserProduct;
use App\Models\Data\Region;
use App\Models\DeleteError;
use App\Models\Finance\Preferential;
use App\Models\NotFound;
use App\Models\ParameterError;
use App\Models\StoreError;
use App\Models\Term\TrainingAudit;
use App\Models\Vehicle\Vehicle;
use Behat\Mink\Session;

use DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Validator;

/**
 * Class User
 *
 * @package App\Models\Admin
 * @property $id
 * @property $user_truename            string      用户真实姓名
 * @property $user_img                 string      用户头像
 * @property $user_sex                 string      用户性别
 * @property $subject_1                string      科目一
 * @property $subject_2                string      科目二
 * @property $subject_3                string      科目三
 * @property $subject_4                string      科目四
 * @property $id_card                  string      身份证号
 * @property $id_card_address
 * @property $user_telphone            string      手机号
 * @property $password                 string      密码
 * @property $pass_salt                string      加密盐值
 * @property $old_province_id          integer     身份证上省份
 * @property $old_city_id              integer     身份证上市
 * @property $old_area_id              integer     身份证上区域
 * @property $new_province_id          integer     现居省份
 * @property $new_city_id              integer     现居市
 * @property $new_area_id              integer     现居区域
 * @property $user_address             string      详细地址
 * @property $user_email               string      用户邮箱
 * @property $school_id                integer     驾校id
 * @property $licence_type_id          integer     驾照类型id
 * @property $class_id                 integer     课程id
 * @property $status                   integer     状态（0 刚报名 1 在读 2 结业 3 退学）
 * @property $apply_date               date        报名日期
 * @property $licenceType
 * @property $finger_id
 * @property $finger_data
 * @property $students_sources         string      学员来源
 * @property $learning_style           string      学习类型
 * @property $balance                  float       余额
 * @property $alternate_user_telephone string      备用手机号
 * @property $nation                   string      民族
 * @property $birthday                 string      生日
 * @property $user_numbers             string      统一编号
 * @property $jpush_id                 string      极光推送id
 * @property $id_card_type             string      身份证类型
 * @property $students_sources_type    string      学员来源类型
 * @property $user_product         \App\Models\Business\UserProduct
 * @method static $this findOrFail($id,array $column = ['*'])
 * @mixin \Eloquent|\Illuminate\Foundation\Auth\User
 */
class User extends Authenticatable {

    // const LOGIN_SUCCESS  = 1; //登陆成功
    // const LOGIN_ERR_USER = 2;//用户错误
    // const LOGIN_ERR_PWD  = 3;//密码错误
    // const LOGIN_ERR_VCOD = 4;//验证码错误
    //
    // const SUB_STA_DO      = 1;//已预约
    // const SUB_STA_CANCEL  = 2;//已取消
    // const SUB_STA_DONE    = 3;//已完成
    // const SUB_STA_BREAK   = 4;//违约
    // const SUB_STA_COMMENT = 6;//已评价

    protected $table   = 'user';
    protected $guarded = ['id','password','pass_salt'];
    protected $hidden = ['password','pass_salt'];
    //加密盐值

    protected $appends=[
            'hk_address',                    //户口所在地
            'xz_address',                    //现居地
            'student_id'
    ];
    protected $rules
        = [
            'user_telphone' => 'sometimes|bail|required|digits:11|unique:user',
            'message'       => 'sometimes|bail|required',
            'password'      => 'sometimes|bail|required|alpha_dash|digits_between:6,8|confirmed',
            'is_agree'      => 'sometimes|bail|accepted',
        ];
    protected $messages
        = [
            'required'             => '该选项必须填写',
            'alpha_dash'           => '请输入字母、数字、下划线',
            'digits_between'       => '长度不符合要求',
            'confirmed'            => '两次密码不一致',
            'accepted'             => '请同意协议',
            'user_telphone.unique' => '手机已被注册',
        ];

    public $id_card_type_string = [
        1 => '身份证',
        2 => '护照',
        3 => '军官证',
        4 => '其他',
    ];

    public $students_sources_type_string = [
        1 => '学员推荐',
        2 => '自主报名',
        3 => '教练推荐',
    ];

    public function userproduct(){
        return $this->belongsTo(UserProduct::class);
    }
    /**
     * [setPasswordAttribute 加密密码]
     *
     * @param [type] $value [description]
     */
    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = $this->encryptPassword($value);
    }

    public function getHkAddressAttribute(){
        return Region::getRegionByCode($this->old_province_id)->name.
               Region::getRegionByCode($this->old_city_id)->name.
               Region::getRegionByCode($this->old_area_id)->name;
    }

    public function getXzAddressAttribute()
    {
        return Region::getRegionByCode($this->new_province_id)->name.
               Region::getRegionByCode($this->new_city_id)->name.
               Region::getRegionByCode($this->new_area_id)->name.
               $this->user_address;
    }

    public function getLastAppointment()
    {
        return $this->appointments()
                    ->where('date', '<', date('Y-m-d', time() + 60 * 60 * 24))
                    ->whereIn('status', [Appointment::STATUS_DONE,Appointment::STATUS_EVALUATED])
                    ->max('date');
    }

    public function getStudentIdAttribute(){
        return data_get($this->user_product, 'student_id', '');
    }

    public function getReceiveCertificateTimeAttribute(){
        return data_get($this->user_product, 'receive_certificate_time', null);
    }

    public function canUsePreferential()
    {
        //todo filter
        return Preferential::getValid($this->school_id);
    }

    /**
     * 根据手机号查找用户
     *
     * @param $mobile
     *
     * @return self
     *
     * @throws \App\Models\NotFound
     */
    public static function findUser($mobile,$fields=array("*")) {
        $user = self::where('user_telphone',$mobile)->select($fields)->first();
        if (!$user) throw new NotFound('用户不存在');
//        Session::
        if($user)
        {
            \Session::put('user_id', $user->id);
            \Session::put('username', $user->user_name);
        }
        return $user;
    }

    /*
     * @Des:  用户信息读取
     * @Parms:$openid
     * */
    public static function findUserByOpenid($openid,$fields=array("*")) {
        return self::where('openid',$openid)->select($fields)->first();
    }


    public static function findUserId($id) {
        $user = self::where('id',$id)->first();
        if (!$user) throw new NotFound('用户不存在');

        return $user;
    }



    public function isMyPassword($password) {
        if ($this->password != $this->encryptPassword($password))
            throw new ParameterError('密码错误');
    }

    public function getGender(){
        return $this->user_sex =='1'?'男':'女';
    }

    /**
     * 获取用户户口所在地
     * @return mixed|string
     */
    public function getProvince(){
        $region = Region::where('code',$this->old_province_id)->first();
        if ($region) return $region->name;
        return '';
    }

    /**
     * @return string
     */
    public function getNewProvince(){
        $region =  Region::where('code',$this->new_province_id)->first();
        if ($region) return $region->name;
        return '';
    }

    /**
     * @desc 修改学员备案状态
     * @return boolean
     */
    public function modifyRecordStatus($uid){

       return  self::where("id", '=', $uid)->update(['record_status' => 2]);

    }

    ##################################--关联关系 start--###########################################

    /**
     * @return  \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
     */
    public function subscribe()
    {
        return $this->hasMany(Appointment::class);
    }




    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany|Admin
     */
    public function admin(){
        return $this->belongsToMany('App\Models\Admin\Admin','user_admin');
    }

    ##################################--关联关系 end--###########################################

    public function showNode(){
        $product = $this->getUsingProduct();

        if($product){
            $stages=DB::table('stage')->get();

            $data = [];
            foreach($stages as $key=>$stage){
                $data[$key]=DB::table('node')->where('stage_id',$stage->id)->where('user_id',$this->id)->get();
                $data[$key]['name']=$stage->name;
            }
            return $data;

        }else{
            return false;
        }
    }

    public static function getSessionKey()
    {
        return parent::getForeignKey();
    }

    public function sublogs($type = NULL, $options = []){
        $new_options = options_filter($options);

        $query = $this->subscribe();

        foreach ($new_options as $key => $value) {
            switch ($key) {
            case 'vehicle_name':
                $query->whereIn('admin_id', Vehicle::where('car_num', 'like', "%$value%")->pluck('id'));
                break;
            case 'admin_name':
                $query->whereIn('admin_id', Admin::where('admin_name', 'like', "%$value%")->pluck('id'));
                break;
            case 'appointment_type_id':
                $query->where('type_id', $value);
                break;
            case 'appointment_date':
                $query->where('date', $value);
                break;
            case 'cancel_date':
                $query->whereBetween('updated_at', [$value . ' 00:00:00', $value . ' 23:59:59']);
                break;
            default:
                $query->where($key, $value);
                break;
            }
        }
        switch ($type) {
            case Appointment::STATUS_DONE:
                $query->where('status', Appointment::STATUS_DONE);
                break;
            case Appointment::STATUS_EVALUATED:
                $query->where('status', Appointment::STATUS_EVALUATED);
                break;
            case Appointment::STATUS_CANCELED:
                $query->where('status', Appointment::STATUS_CANCELED);
                $query->orderBy('updated_at', 'desc');
                break;
            default :
                $query->where('status', '!=', Appointment::STATUS_CANCELED);
                $query->orderBy('date', 'desc');
                break;
        }

        return $query->paginate();
    }


    public function add_comment($data=[])
    {
        return Comment::create($data);
    }

     public function encryptPassword($password) {
         return md5(md5($password).$this->pass_salt);
     }

    /**
     * 获取随机字母或数字
     *
     * @param $length
     *
     * @return string
     */
    function randomKeys($length)
    {
        $returnStr = '';
        $pattern   = '1234567890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLOMNOPQRSTUVWXYZ';
        for ($i = 0; $i < $length; $i++) {
            $returnStr .= $pattern{mt_rand(0, 61)}; //生成php随机数
        }

        return $returnStr;
    }

    /**
     * 关注教练
     *
     * @param $admin_id
     * @param $status
     *
     * @throws AddError
     * @throws DeleteError
     */
    public function changeFollow($admin_id,$status){
        if($status=='add'){
            if(!DB::table('user_admin')->insert(['user_id'=>$this->getKey(),'admin_id'=>$admin_id])){
                throw new AddError();
            }
        }else{
            if(!DB::table('user_admin')->where('user_id',$this->getKey())->where('admin_id',$admin_id)->delete()){
                throw new DeleteError();
            }
        }
    }



    /**
     * @param string $status
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
     */
    public function notifications($status = '')
    {
        /** @var \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder $query */
        $query = $this->hasMany(Notification::class,'to');
        $query->where('from_type',class_basename(self::class));

        switch ($status) {
        case 'READ':
            $query->where('read',Notification::STATUS_READ);
            break;
        case 'UNREAD':
            $query->where('read',Notification::STATUS_UNREAD);
            break;
        default:break;
        }
        $notifications = $query->paginate();

        return $notifications;
    }



    public function charge(float $money){
        if ($money <=0) {
            throw new ParameterError('充值金额应大于0');
        }

        $this->balance += $money;
        $this->save();
    }

    public function balance_pay(Income $income, Preferential $preferential = null) {
        try {
            \DB::beginTransaction();
            $income->pay_money           = $income->money - ($preferential ? $preferential->getReduce($income->money)
                    : 0);
            $income->preferential_detail = json_encode($income->preferential_detail);
            $income->pay_time            = date('Y-m-d H:i:s');
            // todo 在线支付优惠
            $type                = PayType::where('type', PayType::TYPE_BALANCE)->first();
            $income->pay_type_id = $type->id;
            $income->is_paid     = Income::IS_PAID;

            $this->balance -= $income->pay_money;

            if ($this->balance < 0) {
                throw new ParameterError('余额不足');
            }

            $income->save();
            $this->save();

            \DB::commit();
        } catch (\Exception $e) {
            \DB::rollBack();
            throw $e;
        }
    }

    public function fill(array $attributes)
    {
        if (empty($attributes))
            return $this;
        $columns = \Cache::remember($this->table . '_columns', 60,function() {
            return \Schema::getColumnListing($this->table);
        });
        foreach ($attributes as $key=>$value) {
            if (!in_array($key,$columns) || in_array($key, $this->guarded)) {
                unset($attributes[$key]);
            }
        }
        if (!$this->school_id) {
            $this->school_id = session('school_id');
        }

        if (!$this->pass_salt) {
            $this->pass_salt = $this->randomKeys(4);
            $this->password = '123456';
        }

        return parent::fill($attributes);
    }

    public static function register(array $attributes = []) {
        $rules = [
            // 'captcha'       => 'required|confirmed',
            'user_telphone' => 'required|regex:/^1\d{10}$/|unique:user',
            'password'      => 'required|between:6,18|alpha_dash|confirmed',
        ];

        $validator = Validator::make($attributes,$rules);

        if ($validator->fails()) {
            throw new ParameterError($validator->errors()->first());
        }

        $attributes[self::CREATED_AT] = date('Y-m-d H:i:s');
        $attributes[self::UPDATED_AT] = $attributes[self::CREATED_AT];

        $user = new self($attributes);

        if (!$user->save()) {
            throw new StoreError('创建用户失败');
        }

        $user = self::findUser($attributes['user_telphone']);
        return $user;
    }

    public function skill_time(){
        return $this->user_product->skil_time;
    }

    /**
     *
     * @return bool
     * @throws ParameterError
     */
    public function check() {
        $rules = [
            'user_truename'   => 'required|string|between:2,16',
            'user_telphone'   => 'required|unique:user,user_telphone,'.$this->id.'|regex:/^1\d{10}$/',
            'user_sex'        => 'required|in:1,2',
            //'id_card_type'=> 'required|in:1,2,3,4',
            'id_card' => [
                'required', 'unique:user,id_card,' . $this->id, 'regex:/^\d{17}(\d|x|X)$/',
            ],
            'user_email'      => 'email|unique:user,user_email,'.$this->id,
            'new_province_id' => 'required|exists:region,code',
            'new_city_id'     => 'required|exists:region,code',
            'new_area_id'     => 'required|exists:region,code',
            'user_address'    => 'required|string',
            'id_card_address' => 'required|string|min:1',
            //'licence_type_id' => 'required|exists:licence_type,id',
            //'students_sources_type'=> 'required|in:1,2,3',
            // 'students_sources'=> 'required|max:30|min:1',
            // 'installments'    => 'required|max:10|min:1',
            // 'learning_style'  => 'required',
            'alternate_user_telephone' => 'regex:/^1\d{10}$/',
            'nation'          => 'required|max:10|min:1',
            // 'jpush_id'          => 'required|max:50|min:1',
        ];
        $messages = [];
        $customAttributes = [
            'user_truename'   => '姓名',
            'user_telphone'   => '联系电话',
            'user_sex'        => '性别',
            'id_card'         => '身份证号',
            'id_card_address' => '户籍地址',
            'user_email'      => '邮箱',
            'new_province_id' => '现居省',
            'new_city_id'     => '现居市',
            'new_area_id'     => '现居县/区',
            'user_address'    => '详细地址',
            // 'students_sources'=> '学员来源',
            // 'installments'    => '期数',
            // 'learning_style'  => '学习类型',
            'alternate_user_telephone' => '备用手机号',
            'nation'          => '民族',
            'jpush_id'          => '极光推送id',
        ];

        //if (!$this->finger_id || !$this->finger_data) {
        //    throw new ParameterError('指纹信息缺失');
        //}
        //$finger_id_arr = json_decode($this->finger_id, true);
        //$finger_data = explode(',',substr($this->finger_data, 1,-1));
        //$finger_data_arr = $finger_data;
        //if (count($finger_id_arr) != count($finger_data_arr)) {
        //    throw new ParameterError('指纹信息缺失');
        //}
        //if (count($finger_id_arr) < 3) {
        //    throw new ParameterError('需要采集至少三个手指');
        //}

        //$validator = Validator::make($this->toArray();,$rules, $messages, $customAttributes);
        if(!empty($this->original)){
            // 数据修改的时候校验
            // 修改的属性
            $update_attributes = array_diff($this->attributes,$this->original);
            // 修改的key
            $diff_key = array_keys(array_diff($this->attributes,$this->original));
            $rules = array_only($rules,$diff_key);
            $validator = Validator::make($update_attributes, $rules, $messages, $customAttributes);
        }else{
            // 数据添加的时候校验
            $validator = Validator::make($this->toArray(), $rules, $messages, $customAttributes);
        }

        if ($validator->fails()) {
            throw new ParameterError($validator->errors()->first());
        }
        return true;
    }


    /**
     * @param $id
     *
     * @return Admin
     */
    public function coachDetail($id){
        $coach                   = Admin::find($id);
        $coach->is_follow        = $this->admin()->where('admin_id', $id)->first();
        $coach->coach_detail     = $coach->coach_group;
        $coach->vehicle_detail   = $coach->vehicle;
        $coach->appointment_list = $coach->appointmentTypes;
        $coach->vehicle_list     = $coach->vehicle ? $coach->vehicle->appointmentTypes : [];

        return $coach;
    }

    public function signUp(Product $product,\DateTime $start_date, $status = UserProduct::STATUS_CHECKED){

        $data = [
            'user_id'    => $this->id,
            'product_id' => $product->id,
            'start_date' => $start_date,
            'status'     => $status,
            'school_id'  => $product->school_id,
        ];

        //$stage = '报名阶段';
        //$node  = '基本信息录入完成';

        $relation = UserProduct::create($data);
        $relation->save();
    }

    public function remarks(){ return $this->hasMany(Remark::class); }


    /**
     * 获取用户的学时
     */
    public function getTrainingHours(){
        $user_hours = TrainingAudit::where('user_id',$this->id)
            ->orderBy('id','DESC')
            ->first();
        return $user_hours;
    }

    /*+++++++++++++++++++++Joe 新加部分功能++++++++++++++++++++++++++++*/

    /*
     * @Des:    根据身份证号检测学员是否为本校学员
     * @Parms:  $array  身份证号码
     * @Return: $array
     * @Author: Joe
     * */
    public static function GetUserInfo($where=array(),$fields=array('*')){
        return self::where($where)->select($fields)->first();
    }

    /*
     * @Des:    获取学员列表信息
     * @Parms:  $array
     * @Return: $array
     * @Author: Joe
     * */
    public static function GetUserIds($where=array()){
        $query = self::where('school_id','=',session('school_id'));
        if($where['name']){
            $query = $query->where('user_truename', 'like', '%'.$where['name'].'%');
        }
        if($where['id_card']){
            $query = $query->where('id_card', 'like', '%'.$where['id_card'].'%');
        }
        return $query->get();
    }

}
