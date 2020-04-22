<?php namespace App\Library\TCP;

use App\Models\Admin\Admin;
use App\Models\Appointment\Appointment;
use App\Models\Business\UserProduct;
use App\Models\Term\TimingTerm;
use App\Models\Vehicle\Vehicle;
use Illuminate\Support\Facades\Redis;
use App\Models\Home\User;

/**
 * Created by PhpStorm.
 * User: Will
 * Date: 2017/3/28
 * Time: 15:21
 */
class Answer
{
    // 终端注册
    static public function message_0100($header, $body_hex)
    {
        $body = [
            'prov_id' => hexdec(substr($body_hex, 0, 4)),
            'city_id' => hexdec(substr($body_hex, 4, 4)),
            'maker_id' => implode(unpack('a*', hex2bin(substr($body_hex, 8, 10)))),
            'model' => trim(implode(unpack('a*', hex2bin(substr($body_hex, 18, 40))))),
            'no.' => implode(unpack('a*', hex2bin(substr($body_hex, 58, 14)))),
            'imei' => implode(unpack('a*', hex2bin(substr($body_hex, 72, 30)))),
            'color' => hexdec(substr($body_hex, 102, 2)),
            'plate' => mb_convert_encoding(implode(unpack('a*', hex2bin(substr($body_hex, 104)))), 'utf-8', 'gbk'),
        ];

        \Log::debug('body:', $body);

        // done 注册终端
        try {
            $term = new TimingTerm();
            $term->term_type = 1;
            $term->vender = $body['maker_id'];
            $term->sn = $body['no.'];
            $term->imei = $body['imei'];
            $term->model = $body['model'];
            $term->sim = $header['mobile'];


            if ($body['color']) {
                $vehicle = Vehicle::where('car_num', $body['plate'])->first();
                if ($vehicle) {
                    if (TimingTerm::where('vehicle_id', $vehicle->id)->count()) {
                        throw new \Exception('车辆已经注册', 1);
                    }
                    $term->vehicle_id = $vehicle->id;
                    $term->school_id = $vehicle->school_id;
                } else {
                    throw new \Exception('数据库中无该车辆', 2);
                }
            }

            if (TimingTerm::where('imei', $term->imei)->count()) {
                throw new \Exception('终端已被注册', 3);
            }


            $term->getUniqueId();
            $term->save();
            $term->putRecord();
            $term->blind_record($vehicle->vehicle_numbers);
            $message_body = [
                Bin::word($header['message_no']),
                Bin::byte(0),// 结果
                Bin::byte_array('A0107', 5), // 平台编号
                Bin::byte_array($term->school->school_numbers, 16),// 培训机构编号
                Bin::byte_array($term->term_numbers, 16),// 计时终端编号
                Bin::byte_array($term->passwd, 12), // 终端证书口令
                Bin::string($term->key) // 终端证书
            ];

            self::answer('8100', $header['mobile'], $message_body);
        } catch (\Exception $e) {
            \Log::debug('error:' . $e->getMessage());
            $message_body = [
                Bin::word($header['message_no']),
                Bin::byte($e->getCode()),// 结果
            ];

            self::answer('8100', $header['mobile'], $message_body);
        }
    }

    // 心跳
    static public function message_0002($header, $body_hex)
    {

        $message_body = [
            Bin::word($header['message_no']),
            hex2bin($header['message_id']),
            Bin::byte(0)
        ];

        \Log::debug('key:', ['test' => 'this is a test']);


        self::answer('8001', $header['mobile'], $message_body);

    }

    // 终端注销
    static public function message_0003($header, $body_hex)
    {

        $term = TimingTerm::where('sim', $header['mobile'])->first();
        if ($term) {
            // todo 注销终端
            $term->writeOff();
            $term->delete();
        }


        $message_body = [
            Bin::word($header['message_no']),
            hex2bin($header['message_id']),
            Bin::byte(0)
        ];

        self::answer('8001', $header['mobile'], $message_body);

    }

    // 通用
    static public function message_8001($header, $body_hex)
    {
        $message_body = [
            Bin::word($header['message_no']),
            hex2bin($header['message_id']),
            Bin::byte(0)
        ];
        self::answer('8001', $header['mobile'], $message_body);
    }

    // 通用
    static public function message_failed($header, $body_hex)
    {
        $message_body = [
            Bin::word($header['message_no']),
            hex2bin($header['message_id']),
            Bin::byte(1)
        ];
        self::answer('8001', $header['mobile'], $message_body);
    }

    // 通用
    static public function message_error($header, $body_hex)
    {
        $message_body = [
            Bin::word($header['message_no']),
            hex2bin($header['message_id']),
            Bin::byte(2)
        ];
        self::answer('8001', $header['mobile'], $message_body);
    }

    // 通用
    static public function message_unsupposed($header, $body_hex)
    {
        $message_body = [
            Bin::word($header['message_no']),
            hex2bin($header['message_id']),
            Bin::byte(3)
        ];
        self::answer('8001', $header['mobile'], $message_body);
    }

    // 终端鉴权
    static public function message_0102($header, $body_hex)
    {
        $body = [
            'timestamp' => hexdec(substr($body_hex, 0, 8)),
            'key' => implode(unpack('a*', hex2bin(substr($body_hex, 8, 512))))
        ];

        $term = TimingTerm::where('sim', $header['mobile'])->first();
        // todo 终端鉴权
        openssl_pkcs12_read(base64_decode($term->key), $key, $term->passwd);
        $p_key = $key['pkey'];
        #\Log::debug('pkey:'. bin2hex($p_key));
        $hash = null;
        openssl_public_decrypt($body['key'], $hash, $key['cert'], OPENSSL_PKCS1_PADDING);
        $data_bin = pack('a*', $term->term_numbers);
        $timestamp = $body['timestamp'];

        $timestamp_hex = bin2hex(pack('N', $timestamp));

        for ($i = strlen($timestamp_hex); $i < 8; $i++) {
            $timestamp_hex = '0' . $timestamp_hex;
        }
        $timestamp_reverse_bin = hex2bin($timestamp_hex);
        \Log::debug('key:' . bin2hex($body['key']));
        // \Log::debug('timestamp:'. $timestamp);
        // \Log::debug('term_numbers:'. $term->term_numbers);
        \Log::debug('hash:' . bin2hex($hash));
        \Log::debug('hash:' . bin2hex(hash("sha256", $data_bin . $timestamp_reverse_bin, true)));
        #openssl_private_encrypt(bin2hex(hash("sha256", $data_bin.$timestamp_reverse_bin,true)),$encrypt,$p_key);
        #\Log::debug('key:'.bin2hex($encrypt));
        if ($hash == hash("sha256", $data_bin . $timestamp_reverse_bin, true)) {
            //if ($body['key'] === $encrypt){
            $message_body = [
                Bin::word($header['message_no']),
                hex2bin($header['message_id']),
                Bin::byte(0)
            ];
        } else {
            $message_body = [
                Bin::word($header['message_no']),
                hex2bin($header['message_id']),
                Bin::byte(1)
            ];
        }

        self::answer('8001', $header['mobile'], $message_body);
    }

    // 查询终端参数应答
    static public function message_0104($header, $body_hex)
    {

        $body = [
            '消息流水号' => hexdec(substr($body_hex, 0, 4)),
            '应答参数个数' => hexdec(substr($body_hex, 4, 2)),
            '包参数个数' => hexdec(substr($body_hex, 6, 2)),
            '参数像列表' => substr($body_hex, 8),
        ];

        $data = [];
        $data_hex = $body['参数像列表'];
        for ($i = 0; $i < strlen($data_hex);) {
            $key = '0x' . substr($data_hex, $i, 4);
            $i += 4;
            $len = hexdec(substr($data_hex, $i, 2));
            $i += 2;
            $value = substr($data_hex, $i, 2 * $len);
            $i += 2 * $len;
            $data[$key] = $value;
        }

        $message_body = [
            Bin::word($header['message_no']),
            hex2bin($header['message_id']),
            Bin::byte(0)
        ];

        return null;
    }

    // 位置信息汇报
    static public function message_0200($header, $body_hex)
    {
        $body = [
            '报警标识' => hexdec(substr($body_hex, 0, 8)),
            '状态' => hexdec(substr($body_hex, 8, 8)),
            '维度' => hexdec(substr($body_hex, 16, 8)) / 1000000,
            '经度' => hexdec(substr($body_hex, 24, 8)) / 1000000,
            '行驶记录速度' => hexdec(substr($body_hex, 32, 4)) / 10,
            '卫星定位速度' => hexdec(substr($body_hex, 36, 4)) / 10,
            '方向' => hexdec(substr($body_hex, 40, 4)),
            '时间' => substr($body_hex, 44, 12),
            '附加信息' => substr($body_hex, 56),
        ];

        for ($i = 0; $i < strlen($body['附加信息']) - 1;) {
            $id = hexdec(substr($body_hex, 0, 2));
            $length = hexdec(substr($body_hex, 2, 2));
            $value = hexdec(substr($body_hex, 4, $length * 2));
            $i = $i + 4 + $length * 2;
            $body["附加信息$id"] = $value;
        }

        \Log::debug('body:', $body);

        // todo 位置


        $message_body = [
            Bin::word($header['message_no']),
            hex2bin($header['message_id']),
            Bin::byte(0)
        ];

        $message_header = [
            Bin::byte(0),
            hex2bin('8001'),
            pack('n', Bin::length(implode($message_body)) & 0b0000001111111111),
            Bin::bcd_array($header['mobile'], 16),
            Bin::word(1),
            Bin::byte(0),
        ];
        $message = implode(array_merge($message_header, $message_body));
        self::answer('8001', $header['mobile'], $message_body);
        // return $message;
    }

    // 位置信息查询应答
    static public function message_0201($header, $body_hex)
    {
        $body = [
            '报警标识' => hexdec(substr($body_hex, 0, 8)),
            '状态' => hexdec(substr($body_hex, 8, 8)),
            '维度' => hexdec(substr($body_hex, 16, 8)) / 1000000,
            '经度' => hexdec(substr($body_hex, 24, 8)) / 1000000,
            '行驶记录速度' => hexdec(substr($body_hex, 32, 4)) / 10,
            '卫星定位速度' => hexdec(substr($body_hex, 36, 4)) / 10,
            '方向' => hexdec(substr($body_hex, 40, 4)),
            '时间' => substr($body_hex, 44, 12),
            '附加信息' => substr($body_hex, 56),
        ];

        for ($i = 0; $i < strlen($body['附加信息']) - 1;) {
            $id = hexdec(substr($body_hex, 0, 2));
            $length = hexdec(substr($body_hex, 2, 2));
            $value = hexdec(substr($body_hex, 4, $length * 2));
            $i = $i + 4 + $length * 2;
            $body["附加信息$id"] = $value;
        }

        \Log::debug('body:', $body);

        // todo 位置

        return null;
    }

    // 数据上行透传
    static public function message_0900($header, $body_hex)
    {
        $type = substr($body_hex, 0, 2);// 透传消息类型
        $id = substr($body_hex, 2, 4);// 透传消息 ID
        $option = substr($body_hex, 6, 4);// 透传消息属性
        $header['驾培包序号'] = substr($body_hex, 10, 4);// 驾培包序号
        $header['计时终端编号'] = implode(unpack('a*', hex2bin(substr($body_hex, 14, 32))));// 计时终端编号
        $length = hexdec(substr($body_hex, 46, 8));// 数据长度
        $body_hex = substr($body_hex, 54, $length * 2); // 数据内容
        //todo 校验传校验
        $string = substr($body_hex, 54 + $length * 2);// 校验串
        \Log::debug('cross:' . $id);
        $function = 'message_cross_' . $id;
        return self::$function($header, $body_hex);
    }

    // 数据下行透传
    static public function message_8900($header, $body_hex)
    {
    }

    // 平台登录
    static public function message_01f0($header, $body_hex)
    {

        $body = [
            '平台编号' => implode(unpack('a*', hex2bin(substr($body_hex, 0, 10)))),
            '密码' => implode(unpack('a*', hex2bin(substr($body_hex, 10, 16)))),
            '平台接入码' => implode(unpack('a*', hex2bin(substr($body_hex, 26, 4)))),
        ];
        // todo 平台登录
        $message_body = [
            Bin::byte(0)
        ];

        self::answer('81f0', $header['mobile'], $message_body);
    }

    // 平台登出
    static public function message_01f1($header, $body_hex)
    {

        $message_body = [
            Bin::word($header['message_no']),
            hex2bin($header['message_id']),
            Bin::byte(0)
        ];

        self::answer('8001', $header['mobile'], $message_body);
    }

    // 设置终端参数
    static public function message_8103($header, $body_hex)
    {

        $message_body = [
            Bin::word($header['message_no']),
            hex2bin($header['message_id']),
            Bin::byte(0)
        ];

        self::answer('8001', $header['mobile'], $message_body);
    }

    // region 透传类消息

    // 教练登录
    static public function message_cross_0101($header, $body_hex)
    {

        $body = [
            '教练员编号' => implode(unpack('a*', hex2bin(substr($body_hex, 0, 32)))),
            '教练员身份证号' => implode(unpack('a*', hex2bin(substr($body_hex, 32, 36)))),
            '准教车型' => implode(unpack('a*', hex2bin(substr($body_hex, 68, 4)))),
            [
                '报警标识' => hexdec(substr($body_hex, 72, 8)),
                '状态' => hexdec(substr($body_hex, 80, 8)),
                '维度' => hexdec(substr($body_hex, 88, 8)) / 1000000,
                '经度' => hexdec(substr($body_hex, 96, 8)) / 1000000,
                '行驶记录速度' => hexdec(substr($body_hex, 104, 4)) / 10,
                '卫星定位速度' => hexdec(substr($body_hex, 108, 4)) / 10,
                '方向' => hexdec(substr($body_hex, 112, 4)),
                '时间' => substr($body_hex, 116, 12),
            ],
        ];
        \Log::debug('body:', $body);

        // 教练登录
        $admin = Admin::where('admin_numbers', $body['教练员编号'])->first();

        $admin->signIn();

        $message_body = [
            Bin::byte(1),
            Bin::byte_array($body['教练员编号'], 16),
            Bin::byte(0),
            Bin::byte(0)
        ];

        self::answer_cross('8101', $header, $message_body);
    }

    // 教练登出
    static public function message_cross_0102($header, $body_hex)
    {
        \Log::debug('body:' . $body_hex);

        $body = [
            '教练员编号' => implode(unpack('a*', hex2bin(substr($body_hex, 0, 32)))),
            [
                '报警标识' => hexdec(substr($body_hex, 32, 8)),
                '状态' => hexdec(substr($body_hex, 40, 8)),
                '维度' => hexdec(substr($body_hex, 48, 8)) / 1000000,
                '经度' => hexdec(substr($body_hex, 56, 8)) / 1000000,
                '行驶记录速度' => hexdec(substr($body_hex, 64, 4)) / 10,
                '卫星定位速度' => hexdec(substr($body_hex, 68, 4)) / 10,
                '方向' => hexdec(substr($body_hex, 72, 4)),
                '时间' => substr($body_hex, 76, 12),
            ],
        ];
        \Log::debug('body:', $body);
        // todo 教练登出

        $message_body = [
            Bin::byte(1),
            Bin::byte_array($body['教练员编号'], 16),
        ];

        self::answer_cross('8102', $header, $message_body);
    }

    // 学员登录
    static public function message_cross_0201($header, $body_hex)
    {

        $body = [
            '学员编号' => implode(unpack('a*', hex2bin(substr($body_hex, 0, 32)))),
            '当前教练编号' => implode(unpack('a*', hex2bin(substr($body_hex, 32, 32)))),
            '培训课程' => substr($body_hex, 64, 10),
            '课堂ID' => hex2bin(substr($body_hex, 74, 8)),
            [
                '报警标识' => hexdec(substr($body_hex, 82, 8)),
                '状态' => hexdec(substr($body_hex, 90, 8)),
                '维度' => hexdec(substr($body_hex, 98, 8)) / 1000000,
                '经度' => hexdec(substr($body_hex, 106, 8)) / 1000000,
                '行驶记录速度' => hexdec(substr($body_hex, 114, 4)) / 10,
                '卫星定位速度' => hexdec(substr($body_hex, 118, 4)) / 10,
                '方向' => hexdec(substr($body_hex, 122, 4)),
                '时间' => substr($body_hex, 126, 12),
            ],
        ];
        \Log::debug('body:', $body);
        // 学员登录
        $user = User::where('user_numbers', $body['学员编号'])->first();
        /*
        $appointment = Appointment::where('user', $user->id)
                                  ->where('start_time', '<=', date('H:i:s'))
                                  ->where('finish_time', '>', date('H:i:s'))
                                  ->where('date', date('Y-m-d'))
                                  ->whereIn('status', [Appointment::STATUS_TAKEN, Appointment::STATUS_DOING])
                                  ->first();
        $appointment->sign_in();
        */
        $message_body = [
            Bin::byte(1),
            Bin::byte_array($body['学员编号'], 16),
            Bin::word(0), // 总培训学时 * 1 min
            Bin::word(0), // 当前培训部分已完成学时
            Bin::word(0), // 总培训里程 * 0.1 km
            Bin::word(0), // 当前培训部分已完成里程
            Bin::byte(0), // 是否报读附加消息
            Bin::byte(0), // 附加消息长度
        ];

        self::answer_cross('8201', $header, $message_body);
    }

    // 学员登出
    static public function message_cross_0202($header, $body_hex)
    {

        $body = [
            '学员编号' => implode(unpack('a*', hex2bin(substr($body_hex, 0, 32)))),
            '登出时间' => substr($body_hex, 32, 12),
            '该次登录总时间' => hexdec(substr($body_hex, 44, 4)),
            '该次登录总里程' => hexdec(substr($body_hex, 48, 4)),
            '课堂ID' => substr($body_hex, 52, 8),
            [
                '报警标识' => hexdec(substr($body_hex, 60, 8)),
                '状态' => hexdec(substr($body_hex, 68, 8)),
                '维度' => hexdec(substr($body_hex, 76, 8)) / 1000000,
                '经度' => hexdec(substr($body_hex, 84, 8)) / 1000000,
                '行驶记录速度' => hexdec(substr($body_hex, 92, 4)) / 10,
                '卫星定位速度' => hexdec(substr($body_hex, 96, 4)) / 10,
                '方向' => hexdec(substr($body_hex, 100, 4)),
                '时间' => substr($body_hex, 104, 12),
            ],
        ];
        \Log::debug('body:', $body);
        // 学员登出
        $user = User::where('user_numbers', $body['学员编号'])->first();

        /*
        $appointment = Appointment::where('user_id', $user->id)
                                  ->where('date', date('Y-m-d'))
                                  ->whereIn('status', [Appointment::STATUS_TAKEN, Appointment::STATUS_DOING])
                                  ->orderBy('id','desc')
                                  ->first();
        \Log::debug('111:',111);
        $appointment->sign_out($body);
          */
        $message_body = [
            Bin::byte(1),
            Bin::byte_array($body['学员编号'], 16),
        ];

        self::answer_cross('8202', $header, $message_body);
    }

    // 上报学时记录
    static public function message_cross_0203($header, $body_hex)
    {

        $body = [
            '学时记录编号' => implode(unpack('a*', hex2bin(substr($body_hex, 0, 52)))),
            '上报类型' => substr($body_hex, 52, 2),
            '学员编号' => implode(unpack('a*', hex2bin(substr($body_hex, 54, 32)))),
            '教练' => implode(unpack('a*', hex2bin(substr($body_hex, 86, 32)))),
            '课堂ID' => substr($body_hex, 118, 8),
            '记录产生时间' => substr($body_hex, 126, 6),
            '培训课程' => substr($body_hex, 132, 10),
            '记录状态' => hexdec(substr($body_hex, 142, 2)),
            '最大速度' => hexdec(substr($body_hex, 144, 4)),
            '里程' => hexdec(substr($body_hex, 148, 4)),
            '位置信息' => [
                '报警标识' => hexdec(substr($body_hex, 152, 8)),
                '状态' => hexdec(substr($body_hex, 160, 8)),
                '维度' => hexdec(substr($body_hex, 168, 8)) / 1000000,
                '经度' => hexdec(substr($body_hex, 176, 8)) / 1000000,
                '行驶记录速度' => hexdec(substr($body_hex, 184, 4)) / 10,
                '卫星定位速度' => hexdec(substr($body_hex, 188, 4)) / 10,
                '方向' => hexdec(substr($body_hex, 192, 4)),
                '时间' => substr($body_hex, 196, 12),
                '附加信息' => substr($body_hex, 208),
            ],
        ];
        \Log::debug('body:', $body);

        for ($i = 0; $i < strlen($body['位置信息']['附加信息']);) {
            $id = hexdec(substr($body_hex, $i, 2));
            switch ($id) {
                case 1 :
                    $length = hexdec(substr($body_hex, $i + 2, 2));
                    $body['位置信息']['里程'] = hexdec(substr($body_hex, $i + 4, $length * 2));
                    $i = $i + 4 + $length * 2;
                    break;
                case 4 :
                    $length = hexdec(substr($body_hex, $i + 2, 2));
                    $body['位置信息']['发动机转速'] = hexdec(substr($body_hex, $i + 4, $length * 2));
                    $i = $i + 4 + $length * 2;
                    break;
                default :
                    $i = $i + 2;
                    break;
            }
        }

        \Log::debug('body:', $body);
        // todo 更新学时记录
        $appointment = Appointment::find($body['课堂ID']);
        if ($appointment) {
            $appointment->upload();
        }

        self::message_8001($header, null);
    }

    // 命令上报学时应答
    static public function message_cross_0205($header, $body_hex)
    {

        $result = hex2bin($body_hex);

        \Log::debug('body:', $result);
        // todo 通知查询结果
        // 1：查询的记录正在上传；
        // 2：SD卡没有找到；
        // 3：执行成功，但无指定记录；
        // 4：执行成功，稍候上报查询结果
        // 9：其他错误

        return null;
    }

    // 立即拍照应答
    static public function message_cross_0301($header, $body_hex)
    {

        $result = hex2bin(substr($body_hex, 0, 2));
        if ($result != 1) {
            return null;
        } else {
            $data = [
                '上传模式' => hex2bin(substr($body_hex, 2, 2)),
                '通道号' => hex2bin(substr($body_hex, 4, 2)),
                '图片尺寸' => hex2bin(substr($body_hex, 6, 2)),
            ];
        }

        \Log::debug('body:', $result);
        // todo 通知查询结果
        // 1：查询的记录正在上传；
        // 2：SD卡没有找到；
        // 3：执行成功，但无指定记录；
        // 4：执行成功，稍候上报查询结果
        // 9：其他错误

        return null;
    }

    // 查询照片应答
    static public function message_cross_0302($header, $body_hex)
    {

        $result = hex2bin(substr($body_hex, 0, 2));

        \Log::debug('body:', $result);
        // todo 通知查询结果
        // 1：开始查询
        // 2：执行失败
        // 9：其他错误

        return null;
    }

    // 查询照片结果
    static public function message_cross_0303($header, $body_hex)
    {

        $is_end = hex2bin(substr($body_hex, 0, 2));
        $count = hex2bin(substr($body_hex, 2, 2));
        if ($count > 0) {
            $num = hex2bin(substr($body_hex, 4, 2));
            $adta = [];
            for ($i = 6; $i < strlen($body_hex); $i = $i + 20) {
                $data[] = implode(unpack('a*', substr($body_hex, $i, 20)));
            }
        }

        $body = Bin::byte(0);
        self::answer_cross('8303', $header, $body);
    }

    // 上传指定照片应答
    static public function message_cross_0304($header, $body_hex)
    {

        $result = hexdec($body_hex);
        // todo 通知结果
        // 0：找到照片，稍候上传；
        // 1：没有该照片；
        // 9：其他错误

        return null;
    }

    // 上传照片初始化
    static public function message_cross_0305($header, $body_hex)
    {
        $body = [
            '照片编号' => implode(unpack('a*', substr($body_hex, 0, 20))),
            '教练或学员编号' => implode(unpack('a*', substr($body_hex, 20, 32))),
            '摄像头通道号' => hexdec(substr($body_hex, 52, 2)),
            '上传模式' => hexdec(substr($body_hex, 54, 2)),
            '图片尺寸' => hexdec(substr($body_hex, 56, 2)),
            '发起图片的事件类型' => hexdec(substr($body_hex, 58, 2)),
            '总包数' => hexdec(substr($body_hex, 60, 4)),
            '照片数据大小' => hexdec(substr($body_hex, 64, 8)),
            '课堂ID' => hexdec(substr($body_hex, 72, 8)),
            '位置信息' => [
                '报警标识' => hexdec(substr($body_hex, 80, 8)),
                '状态' => hexdec(substr($body_hex, 88, 8)),
                '维度' => hexdec(substr($body_hex, 96, 8)) / 1000000,
                '经度' => hexdec(substr($body_hex, 104, 8)) / 1000000,
                '行驶记录速度' => hexdec(substr($body_hex, 112, 4)) / 10,
                '卫星定位速度' => hexdec(substr($body_hex, 116, 4)) / 10,
                '方向' => hexdec(substr($body_hex, 120, 4)),
                '时间' => substr($body_hex, 124, 12),
                '附加信息' => substr($body_hex, 136, 20),
            ],
            '人脸识别置信度' => hex2bin(substr($body_hex, 156, 2)),
        ];

        for ($i = 0; $i < strlen($body['位置信息']['附加信息']);) {
            $id = hexdec(substr($body_hex, $i, 2));
            switch ($id) {
                case 1 :
                    $length = hexdec(substr($body_hex, $i + 2, 2));
                    $body['位置信息']['里程'] = hexdec(substr($body_hex, $i + 4, $length * 2));
                    $i = $i + 4 + $length * 2;
                    break;
                case 4 :
                    $length = hexdec(substr($body_hex, $i + 2, 2));
                    $body['位置信息']['发动机转速'] = hexdec(substr($body_hex, $i + 4, $length * 2));
                    $i = $i + 4 + $length * 2;
                    break;
                default :
                    $i = $i + 2;
                    break;
            }
        }

        $message_body = Bin::byte(0);

        self::answer_cross('8305', $header, $message_body);
    }

    // 上传照片数据包
    static public function message_cross_0306($header, $body_hex)
    {
        $num = implode(unpack('a*', substr($body_hex, 0, 20)));
        $image = hex2bin(substr($body_hex, 20));
        \Log::debug('image_num:' . $num);
        // todo 存储照片
        // file_put_contents(public_path('test_image.jpg'),$image);

        self::message_8001($header, null);
    }

    // 设置计时终端应用参数应答
    static public function message_cross_0501($header, $body_hex)
    {

        $result = hexdec($body_hex);

        // todo 通知结果

        return null;
    }

    // 设置禁训状态应答
    static public function message_cross_0502($header, $body_hex)
    {

        $body = [
            '执行结果' => hexdec(substr($body_hex, 0, 2)),
            '禁训状态' => hexdec(substr($body_hex, 2, 2)),
            '提示消息长度' => hexdec(substr($body_hex, 4, 2)),
            '提示消息内容' => implode(unpack('a*', substr($body_hex, 6))),
        ];
        // todo 通知结果
        return null;
    }

    // 设置计时终端应用参数应答
    static public function message_cross_0503($header, $body_hex)
    {

        $body = [
            '查询结果' => hexdec(substr($body_hex, 0, 2)),
            '定时拍照时间间隔' => hexdec(substr($body_hex, 2, 2)),
            '照片上传设置' => hexdec(substr($body_hex, 4, 2)),
            '是否报读附加消息' => hexdec(substr($body_hex, 6, 2)),
            '熄火后停止学时计时的延时时间' => hexdec(substr($body_hex, 8, 2)),
            '熄火后GNSS数据包上传间隔' => hexdec(substr($body_hex, 10, 4)),
            '熄火后教练自动登出的延时时间' => hexdec(substr($body_hex, 14, 4)),
            '重新验证身份时间' => hexdec(substr($body_hex, 18, 4)),
            '教练跨校教学' => hexdec(substr($body_hex, 22, 2)),
            '学员跨校学习' => hexdec(substr($body_hex, 24, 2)),
            '相应平台同类消息时间间隔' => hexdec(substr($body_hex, 26, 4)),
        ];
        // 通知结果
        return null;
    }

    // 身份认证
    static public function message_cross_0401($header, $body_hex)
    {

        $data = [
            '请求信息类型' => hex2bin(substr($body_hex, 0, 2)),
            '请求人员类型' => hex2bin(substr($body_hex, 2, 2)),
            '身份证号码' => implode(unpack('a*', substr($body_hex, 4)))
        ];

        // todo 身份认证
        $content = Bin::string('');
        $message_body = [
            Bin::word($header['驾培包序号']),
            Bin::byte(0),
            Bin::length($content),
            $content
        ];

        self::answer_cross('8401', $header, $message_body);
    }

    // 统一编号
    static public function message_cross_0402($header, $body_hex)
    {

        $data = [
            '统一编号类型' => hex2bin(substr($body_hex, 0, 2)),
            '统一编号检索字段' => hex2bin(substr($body_hex, 2))
        ];

        // todo 查找统一编号
        $content = '';
        $message_body = [
            Bin::word($header['驾培包序号']),
            Bin::byte(0),
            Bin::byte_array($content, 16),
            Bin::byte_array('C1', 2),
        ];

        self::answer_cross('8402', $header, $message_body);
    }

    // 车辆绑定信息
    static public function message_cross_0403($header, $body_hex)
    {

        $data = implode(unpack('a*', hex2bin($body_hex)));

        // todo 车辆绑定
        $plate = '';
        $message_body = [
            Bin::word($header['驾培包序号']),
            Bin::byte(0),
            Bin::byte(1),
            Bin::string($plate)
        ];

        self::answer_cross('8403', $header, $message_body);
    }
    // endregion

    // message template
    static public function message($header, $body_hex)
    {

        $message_body = [
            Bin::word($header['message_no']),
            hex2bin($header['message_id']),
            Bin::byte(0)
        ];
        self::answer('8001', $header['mobile'], $message_body);
    }

    // done 添加分包参数
    // 消息入队
    private static function answer($id, $mobile, $body, $num = 0, $total = 0)
    {

        if (!is_array($body)) $body = [$body];
        if (Bin::length(implode($body)) > 2000) {
            // done 分包
            $body_hex = bin2hex(implode($body));
            // \Log::debug('总包:'.$body_hex);
            $total = ceil(strlen($body_hex) / 1024);
            for ($j = 0; $j < $total; $j++) {
                $data = hex2bin(substr($body_hex, $j * 1024, 1024));
                // done 重新构建包
                self::answer($id, $mobile, $data, $j + 1, $total);
                \Log::debug('分包' . ($j + 1) . '/' . $total);
            }
            return;
        }
        // done 构建消息头
        $redis = Redis::connection();
        /*  @var $redis \Redis */
        $option = $num ? 0b0010000000000000 : 0b0000000000000000;

        if (!$redis->exists('message_serial_number')) {
            $redis->set('message_serial_number', 0);
        }

        $serial_num = $redis->get('message_serial_number');

        if ($serial_num >= 0xffff) {
            $redis->set('message_serial_number', 0);
        } else {
            $redis->incr('message_serial_number');
        }

        $message_header = [
            Bin::byte(0),// 消息版本号
            hex2bin($id),// 消息ID
            pack('n', Bin::length(implode($body)) ^ $option),// 消息体属性
            Bin::bcd_array($mobile, 16),//终端手机号
            Bin::word($serial_num),// 消息流水号
            Bin::byte(0),// 预留
        ];
        if ($num && $total) {
            $message_header[] = Bin::word($total); //分包总数
            $message_header[] = Bin::word($num); // 分包序号
        }
        $message = implode(array_merge($message_header, $body));

        $node_id = $redis->hGet('tcp_nodes', $mobile);
        $redis->lPush('message_list_' . $node_id, bin2hex(Bin::pack($message)));
        \Log::debug('answer:' . bin2hex(Bin::pack($message)));
        // return $message;
    }

    private static function answer_cross($id, $header, $body)
    {
        if (!is_array($body)) $body = [$body];
        $message_body = [
            hex2bin('13'),
            hex2bin($id),
            Bin::word(0),
            Bin::word(1),
            Bin::byte_array($header['计时终端编号'], 16),
            Bin::dword(Bin::length(implode($body))),
        ];
        $message_body = array_merge($message_body, $body);
        self::answer('8900', $header['mobile'], $message_body);
    }

    static public function __callStatic($name, $arguments)
    {
        self::message_unsupposed(...$arguments);
    }
}
