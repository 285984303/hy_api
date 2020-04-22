<?php
/**
 * Created by PhpStorm.
 * User: Will
 * Date: 26/09/2016
 * Time: 3:00 PM
 */

namespace App\Models\Data;


use App\Models\Admin\Admin;
use App\Models\Appointment\Appointment;
use App\Models\Appointment\AppointmentType;
use App\Models\Business\Product;
use App\Models\Business\UserProduct;
use App\Models\Finance\Income;
use App\Models\Home\User;
use App\Models\Student\Examination;
use App\Models\Vehicle\Vehicle;
use Illuminate\Database\Eloquent\Model;
use App\Models\BaseModel;

class Statistics extends BaseModel
{
    protected $table = 'statistics_hours';

    /**[废弃]
     * @param array $options
     * @param array $groups
     * @param array $orders
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public static function hours($options = [], $groups = [], $orders = [])
    {
        $query = \DB::table('statistics_hours');

        $admin_ids = Admin::where('school_id', session('school_id'))->pluck('id');
        $query->whereIn('admin_id', $admin_ids);

        $options = options_filter($options);
        if (in_array('day', $groups)) {
            $groups[] = 'month';
        }
        if (in_array('month', $groups)) {
            $groups[] = 'year';
        }
        $groups = array_unique(array_filter($groups, function ($value) use ($options) {
            return !in_array($value, array_keys($options)) && !empty($value);
        }));

        foreach ($options as $key => $value) {
            switch ($key) {
                case 'start_date' :
                    $query->where('date', '>=', $value);
                    break;
                case 'finish_date' :
                    $query->where('date', '<=', $value);
                    break;
                case 'subject' :
                    $ids = [];
                    if ($value == 2) $ids = AppointmentType::$subject_2_type;
                    if ($value == 3) $ids = AppointmentType::$subject_3_type;
                    $query->whereIn('type_id', $ids);
                    break;
                case 'user_truename' :
                    $user_ids = User::where($key, 'like', "%$value%")->pluck('id');
                    $query->whereIn('user_id', $user_ids);
                    break;
                case 'student_id' :
                    $user_ids = UserProduct::where($key, 'like', "%$value%")->pluck('id');
                    $query->whereIn('user_id', $user_ids);
                    break;
                case 'admin_name' :
                    $admin_ids = Admin::where($key, 'like', "%$value%")->pluck('id');
                    $query->whereIn('admin_id', $admin_ids);
                    break;
                case 'staff_id' :
                    $admin_ids = Admin::where($key, 'like', "%$value%")->pluck('id');
                    $query->whereIn('admin_id', $admin_ids);
                    break;
                default :
                    $query->where($key, $value);
                    break;
            }
        }

        $select = 'sum(hours) as hours';
        foreach ($groups as $key) {
            switch ($key) {
                default :
                    $select .= ',' . $key;
                    $query->groupBy($key);
                    break;
            }
        }
        $orders = array_filter($orders);
        foreach ($orders as $key => $value) {
            if (is_int($key)) {
                $query->orderBy($value);
            } else {
                $query->orderBy($key, $value);
            }
        }
        $query->selectRaw($select);

        return $query;
    }

    public static function income($options = [], $groups = [], $orders = [])
    {
        $options = options_filter($options);
        if (in_array('day', $groups)) {
            $groups[] = 'month';
        }
        $query = \DB::table('statistics_income');
        foreach ($options as $key => $value) {
            switch ($key) {
                case 'start_date' :
                    $query->where('date', '>=', $value);
                    break;
                case 'finish_date' :
                    $query->where('date', '<=', $value);
                    break;
                default :
                    $query->where($key, $value);
                    break;
            }
        }
        foreach ($groups as $key) {
            switch ($key) {
                default :
                    // $select .= ','.$key;
                    $query->groupBy($key);
                    break;
            }
        }
        return $query;
    }

    // 教练带教数据统计
    public static function coachTeaching($options = [], $is_export = false)
    {
        $options_coach = [
            'admin_name' => $options['admin_name'] ?? "",
            'staff_id' => $options['staff_id'] ?? "",
        ];
        $coaches = Admin::getAllCoaches(session('school_id'), ['id', 'admin_name', 'staff_id'], options_filter($options_coach));

        $users = User::where(function ($query) use ($options) {
            if (isset($options['user_truename']))
                $query->where('user_truename', 'like', '%' . $options['user_truename'] . '%');
        })->get(['id', 'user_truename']);

        $exams = Examination::where(function ($query) {
            $query->where('school_id', session('school_id'))
                ->where('date', '<=', request('exam_finish_date', date('Y-m-d', strtotime('+1 day'))))
                ->where('date', '>=', request('exam_start_date', date('Y-m-d', 0)));
            if (request('subject'))
                $query->where('subject', request('subject'));
            if (request('exam_count')) {
                if (is_numeric(request('exam_count'))) {
                    $user_ids = Examination::where('school_id', session('school_id'))->groupBy('user_id')
                        ->groupBy('subject')
                        ->havingRaw('COUNT(`id`) = ' . request('exam_count'))
                        ->distinct()->pluck('user_id');
                    $query->whereIn('user_id', $user_ids);
                }
            }
        })->get();
        $user_product_relations = UserProduct::where(function ($query) use ($users, $exams) {
            $query->where('school_id', session('school_id'))
                ->whereIn('user_id', $users->pluck('id'));
            if (request('student_id'))
                $query->where('student_id', 'like', '%' . request('student_id') . '%');
            if (request('product_id'))
                $query->where('product_id', request('product_id'));
            if (request('exam_finish_date') || request('exam_start_date'))
                $query->whereIn('user_id', $exams->pluck('user_id'));
        })->get();

        $coach_student = Appointment::where(function ($query) use ($coaches, $user_product_relations) {
            $query->whereIn('status', [Appointment::STATUS_DONE, Appointment::STATUS_BROKEN, Appointment::STATUS_EVALUATED])
                ->whereIn('admin_id', $coaches->pluck('id'))
                ->whereIn('user_id', $user_product_relations->pluck('user_id'))
                ->where('school_id', session('school_id'));
            if (request('subject') == Examination::SUBJECT_2) {
                $query->whereIn('type_id', AppointmentType::$subject_2_type);
            } elseif (request('subject') == Examination::SUBJECT_3) {
                $query->whereIn('type_id', AppointmentType::$subject_3_type);
            }
            if (request('appoint_finish_date') || request('appoint_start_date')) {
                $query->where('date', '<=', request('appoint_finish_date', date('Y-m-d', strtotime('+1 day'))));
                $query->where('date', '>=', request('appoint_start_date', date('Y-m-d', 0)));
            }
        })->distinct()->orderBy('admin_id')->orderBy('user_id')->groupBy(['admin_id', 'user_id'])
            ->select(['admin_id', 'user_id']);

        if ($is_export) {
            $coach_student = $coach_student->get();
        } else {
            $coach_student = $coach_student->paginate();
        }

        foreach ($coach_student as &$relation) {
            $admin = $relation->admin;
            $user = $relation->user;
            $relation->appointments = Appointment::whereIn('status', [Appointment::STATUS_EVALUATED, Appointment::STATUS_BROKEN, Appointment::STATUS_DONE])
                ->where('admin_id', $admin->id)->where('user_id', $user->id)->get();
            $relation->exams = Examination::where('user_id', $user->id)->where('school_id', session('school_id'))->get();
            // 考试次数	考试日期	学员评分	带教学时	考试学时	合格学时
            $relation->subject_2 = new \stdClass();
            $relation->subject_2->stage = '科目二';
            $relation->subject_2->exams_count = $relation->exams->where('subject', Examination::SUBJECT_2)->count(); // 考试次数
            $relation->subject_2->exams_last_date = $relation->exams->where('subject', Examination::SUBJECT_2)->max('date'); // 最后考试日期
            $relation->subject_2->exams_last_unpass_date = $relation->exams->where('subject', Examination::SUBJECT_2)->filter(function ($value) {
                return $value < 60;
            })->max('date'); // 最后未及格考试日期
            $relation->subject_2->exams_pass_date = $relation->exams->where('subject', Examination::SUBJECT_2)->filter(function ($value) {
                return $value >= 60;
            })->max('date'); // 最后未及格考试日期
            $comment_score = 0;
            $comment_count = 0;
            $relation->subject_2->hours = 0;
            $relation->subject_2->exams_hours = 0;
            $relation->subject_2->exams_pass_hours = 0;
            foreach ($relation->appointments as $appointment) {
                if (in_array($appointment->type_id, AppointmentType::$subject_2_type)) {
                    $relation->subject_2->hours += (strtotime($appointment->finish_time) - strtotime($appointment->start_time)) / 3600; // 带教学时
                    if ($relation->subject_2->exams_last_date && $appointment->date <= $relation->subject_2->exams_last_date) {
                        $relation->subject_2->exams_hours += (strtotime($appointment->finish_time) - strtotime($appointment->start_time)) / 3600;
                    } // 考试学时
                    if ($relation->subject_2->exams_pass_date) {
                        if ($relation->subject_2->exams_pass_date >= $appointment->date
                            && ($relation->subject_2->exams_last_unpass_date
                                ? $relation->subject_2->exams_pass_date > $appointment->date : TRUE)
                        ) {
                            $relation->subject_2->exams_pass_hours += (strtotime($appointment->finish_time) - strtotime($appointment->start_time)) / 3600; // 合格学时
                        }
                    }

                    if ($appointment->comment) {
                        $comment_score += intval($appointment->comment->general);
                        $comment_count++;
                    }
                }
            }
            $relation->subject_2->score = $comment_count ? $comment_score / $comment_count : 0; // 学员评分

            $relation->subject_3 = new \stdClass();
            $relation->subject_3->stage = '科目三';
            $relation->subject_3->exams_count = $relation->exams->where('subject', Examination::SUBJECT_2)->count(); // 考试次数
            $relation->subject_3->exams_last_date = $relation->exams->where('subject', Examination::SUBJECT_2)->max('date'); // 最后考试日期
            $relation->subject_3->exams_last_unpass_date = $relation->exams->where('subject', Examination::SUBJECT_2)->filter(function ($value) {
                return $value < 60;
            })->max('date'); // 最后未及格考试日期
            $relation->subject_3->exams_pass_date = $relation->exams->where('subject', Examination::SUBJECT_2)->filter(function ($value) {
                return $value >= 60;
            })->max('date'); // 最后未及格考试日期
            $comment_score = 0;
            $comment_count = 0;
            $relation->subject_3->hours = 0;
            $relation->subject_3->exams_hours = 0;
            $relation->subject_3->exams_pass_hours = 0;
            foreach ($relation->appointments as $appointment) {
                if (in_array($appointment->type_id, AppointmentType::$subject_3_type)) {
                    $relation->subject_3->hours += (strtotime($appointment->finish_time) - strtotime($appointment->start_time)) / 3600; // 带教学时
                    if ($relation->subject_3->exams_last_date && $appointment->date <= $relation->subject_3->exams_last_date) {
                        $relation->subject_3->exams_hours += (strtotime($appointment->finish_time) - strtotime($appointment->start_time)) / 3600;
                    } // 考试学时
                    if ($relation->subject_3->exams_pass_date) {
                        if ($relation->subject_3->exams_pass_date >= $appointment->date
                            && ($relation->subject_3->exams_last_unpass_date
                                ? $relation->subject_3->exams_pass_date > $appointment->date : TRUE)
                        ) {
                            $relation->subject_3->exams_pass_hours += (strtotime($appointment->finish_time) - strtotime($appointment->start_time)) / 3600; // 合格学时
                        }
                    }

                    if ($appointment->comment) {
                        $comment_score += $appointment->comment->general;
                        $comment_count++;
                    }
                }
            }
            $relation->subject_3->score = $comment_count ? $comment_score / $comment_count : 0; // 学员评分
        }
        return $coach_student;
    }


    /**
     * 学员带教学时统计
     */
    public static function studentTestScores($options = [])
    {
        $query = Examination::where(function ($query) use ($options) {
            $subject = isset($options['subject']) ? $options['subject'] : Examination::SUBJECT_1;
            $query->where('school_id', session('school_id'))
                ->where('subject', $subject);

            foreach ($options as $key => $value) {
                switch ($key) {
                    case 'user_truename':
                        $user_ids = User::where('user_truename', 'like', "%$value%")
                            ->pluck('id');
                        $query->whereIn('user_id', $user_ids);
                        break;
                    case 'id_card':
                        $user_ids = User::where('id_card', 'like', "%$value%")
                            ->pluck('id');
                        $query->whereIn('user_id', $user_ids);
                        break;
                    case 'user_telphone':
                        $user_ids = User::where('user_telphone', 'like', "%$value%")
                            ->pluck('id');
                        $query->whereIn('user_id', $user_ids);
                        break;

                    case 'student_id':
                        $user_ids = UserProduct::where('school_id', session('school_id'))
                            ->where('student_id', 'like', "%$value%")
                            ->pluck('user_id');
                        $query->whereIn('user_id', $user_ids);
                        break;
                    case 'product_id':
                        $user_ids = UserProduct::where('school_id', session('school_id'))
                            ->where('product_id', $value)
                            ->pluck('user_id');
                        $query->whereIn('user_id', $user_ids);
                        break;
                    case 'licence_type_id':
                        /*$product_ids = Product::where('school_id', session('school_id'))->where('licence_type_id',$value)->pluck('id');
                        $user_ids = UserProduct::where('school_id', session('school_id'))
                            ->whereIn('product_id', $product_ids)
                            ->pluck('user_id');
                        $query->whereIn('user_id', $user_ids);*/
                        $query->where('licence_type', '=', $value);
                        break;
                    case 'exam_result':
                        $query->where('is_ok', '=', $value);
                        /*if($value == 'PASS'){
                            $query->where('is_ok','=',1);
                        }else{
                            $query->where('is_ok','=',2);
                        }*/
                        break;
                    case 'start_date':
                        $query->where('date', '>=', $value);
                        break;
                    case 'finish_date':
                        $query->where('date', '<=', $value);
                        break;
                    default:
                        ;
                }
            }

        })->selectRaw('user_id, max(score) as score, max(date) as date,count(id) as count,subject,licence_type')
            ->groupBy('user_id')
            ->orderBy('date', 'DESC');
        if (isset($options['exam_count'])) {
            if (is_numeric($options['exam_count'])) {
                $query->havingRaw('COUNT(`id`) = ' . $options['exam_count']);
            }
        }
        return $query;
    }


    /**
     * 有效学时计算
     */
    public static function appointments($options = [])
    {
        $appointments_query = Appointment::where(function ($query) use ($options) {
            $query->where('school_id', session('school_id'))
                ->whereIn('status', [Appointment::STATUS_DONE, Appointment::STATUS_EVALUATED]);
//                ->whereIn('status', [Appointment::STATUS_EVALUATED]);
            foreach ($options as $key => $value) {
                switch ($key) {
                    case 'subject':
                        $query->where('type_id', $value);
                        break;
                    case 'date':
                        $query->where('date', $value);
                        break;
                    case 'start_date':
                        $query->where('date', '>=', $value);
                        break;
                    case 'end_date':
                        $query->where('date', '<=', $value);
                        break;
                    case 'admin_name':
                        $admin_ids = Admin::where('admin_name', 'like', "%$value")->where('school_id', session('school_id'))->pluck('id');
                        $query->whereIn('admin_id', $admin_ids);
                        break;
                    case 'admin_id':
                        //$admin_ids = Admin::where('admin_name', 'like', "%$value")->where('school_id', session('school_id'))->pluck('id');
                        $query->where('admin_id','=', $value);
                        break;
                    case 'user_truename':
                        $student_ids = User::where('user_truename', 'like', '%' . $value . '%')->where('school_id', session('school_id'))->pluck('id');
                        $query->whereIn('user_id', $student_ids);
                        break;
                    case 'car_num':
                        $car_id = Vehicle::where('car_num', 'like', '%' . $value . '%')->pluck('id');
                        $query->whereIn('vehicle_id', $car_id);
                        break;
                    default:
                        ;
                }
            }

        })->orderBy('date', 'DESC')->orderBy('id', 'DESC');
        return $appointments_query;
    }
    
    
    /**
     * 违约学时计算
     */
    public static function appointments_no($options = [])
    {
        $appointments_query = Appointment::where(function ($query) use ($options) {
            $query->where('school_id', session('school_id'))
            ->where('status', Appointment::STATUS_BROKEN);
            //                ->whereIn('status', [Appointment::STATUS_EVALUATED]);
            foreach ($options as $key => $value) {
                switch ($key) {
                    case 'subject':
                        $query->where('type_id', $value);
                        break;
                    case 'date':
                        $query->where('date', $value);
                        break;
                    case 'start_date':
                        $query->where('date', '>=', $value);
                        break;
                    case 'end_date':
                        $query->where('date', '<=', $value);
                        break;
                    case 'admin_name':
                        $admin_ids = Admin::where('admin_name', 'like', "%$value")->where('school_id', session('school_id'))->pluck('id');
                        $query->whereIn('admin_id', $admin_ids);
                        break;
                    case 'admin_id':
                        //$admin_ids = Admin::where('admin_name', 'like', "%$value")->where('school_id', session('school_id'))->pluck('id');
                        $query->where('admin_id','=', $value);
                        break;
                    case 'user_truename':
                        $student_ids = User::where('user_truename', 'like', '%' . $value . '%')->where('school_id', session('school_id'))->pluck('id');
                        $query->whereIn('user_id', $student_ids);
                        break;
                    case 'car_num':
                        $car_id = Vehicle::where('car_num', 'like', '%' . $value . '%')->pluck('id');
                        $query->whereIn('vehicle_id', $car_id);
                        break;
                    default:
                        ;
                }
            }
            
        })->orderBy('date', 'DESC')->orderBy('id', 'DESC');
        return $appointments_query;
    }


    /**
     * 学员预约统计
     */
    public static function reservation($options = [])
    {
        $appointments_query = Appointment::where(function ($query) use ($options) {
            $query->where('school_id', session('school_id'));

            foreach ($options as $key => $value) {
                switch ($key) {
                    case 'user_truename':
                        $user_ids = User::where('user_truename', 'like', "%$value%")->pluck('id');
                        $query->whereIn('user_id', $user_ids);
                        break;
//                    case 'user_telphone':
//                        $user_ids = User::where('user_telphone', 'like', "%$value%")->pluck('id');
//                        $query->whereIn('user_id', $user_ids);
//                        break;
                    case 'admin_name':
                        $admin_ids = Admin::where('admin_name', 'like', "%$value%")->where('school_id', session('school_id'))->pluck('id');
                        $query->whereIn('admin_id', $admin_ids);
                        break;
//                    case 'mobile_phone':
//                        $admin_ids = Admin::where('mobile_phone', 'like', "%$value%")->where('school_id', session('school_id'))->pluck('id');
//                        $query->whereIn('admin_id', $admin_ids);
//                        break;
                    case 'car_num':
                        $vehicle_ids = Vehicle::where('car_num', 'like', "%$value%")->where('school_id', session('school_id'))->pluck('id');
                        $query->whereIn('vehicle_id', $vehicle_ids);
                        break;
                    case 'licence_type':
                        $vehicle_ids = Vehicle::where('licence_type_id', $value)->where('school_id', session('school_id'))->pluck('id');
                        $query->whereIn('vehicle_id', $vehicle_ids);
                        break;
                    case 'handle_admin_name':
                        $admin_ids = Admin::where('admin_name', 'like', "%$value%")->where('school_id', session('school_id'))->pluck('id');
                        $query->whereIn('handle_admin_id', $admin_ids);
                        break;
                    case 'appointment_type_id':
                        (request('appointment_type_id') == 2) ? $query->whereIn('type_id', [1, 2, 3]) : $query->whereIn('type_id', [4, 5, 6]);
                        break;
                    case 'status':
                        if (is_array($value)) {
                            $query->whereIn('status', $value);
                        } else {
                            $query->where('status', $value);
                        }
                        break;
                    case 'start_date':
                        $query->where('date', '>=', $value);
                        break;
                    case 'end_date':
                        $query->where('date', '<=', $value);
                        break;
                    default:
                        ;
                }
            }
        });
        return $appointments_query;
    }


    /**
     * 报名费用
     */
    public static function recruit($options = [])
    {
        $query = UserProduct::where(function ($query) use ($options) {
            $income_ids = Income::where('is_paid', 'T')->where('school_id', session('school_id'))->where('income_type_id', IncomeType::where('type', 'REGISTER')->first()->id)->pluck('id');
            $query->whereIn('income_id', $income_ids);

            foreach ($options as $key => $value) {
                switch ($key) {
                    case 'user_truename':
                        $user_ids = User::where('user_truename', 'like', "%$value%")->pluck('id');
                        $query->whereIn('user_id', $user_ids);
                        break;
                    case 'id_card':
                        $user_ids = User::where('id_card', 'like', "%$value%")->pluck('id');
                        $query->whereIn('user_id', $user_ids);
                        break;
                    case 'user_telphone':
                        $user_ids = User::where('user_telphone', 'like', "%$value%")->pluck('id');
                        $query->whereIn('user_id', $user_ids);
                        break;
                    case 'handle_admin_id':
                        $admin_ids = Admin::where('school_id', session('school_id'))->where('id', $value)->pluck('id');
                        $query->whereIn('handle_admin_id', $admin_ids);
                        break;
                    case 'income_admin_id':
                        $admin_ids = Admin::where('school_id', session('school_id'))->where('id', $value)->pluck('id');
                        $income_ids = Income::whereIn('handle_admin_id', $admin_ids)->pluck('id');
                        $query->whereIn('income_id', $income_ids);
                        break;
                    case 'product_id':
                        $query->where('product_id', request('product_id'));
                        break;
                    case 'licence_type_id':
                        $product_ids = Product::where('school_id', session('school_id'))->where('licence_type_id', $value)->pluck('id');
                        $query->whereIn('product_id', $product_ids);
                        break;
                    case 'start_date':
                        $query->where('start_date', '>=', $value);
                        break;
                    case 'finish_date':
                        $query->where('start_date', '<=', $value);
                        break;
                    default:
                        ;
                }
            }
        })->orderBy('start_date', 'desc');

        return $query;
    }


    /**
     * 训练收费统计
     */
    public static function appointment_income($options = [])
    {
        $query = Appointment::where(function ($query) use ($options) {
            $query->whereIn('status', [Appointment::STATUS_DONE, Appointment::STATUS_BROKEN, Appointment::STATUS_EVALUATED])
                ->where('school_id', session('school_id'));

            $query->where(function ($query) use ($options) {
                $query->where('start_time', '<', $options['finish_time'] ?? '23:59:59');
                $query->where('start_time', '>=', $options['start_time'] ?? '00:00:00');
                $query->orWhere(function ($query) use ($options) {
                    $query->where('finish_time', '<=', $options['finish_time'] ?? '23:59:59');
                    $query->where('finish_time', '>', $options['start_time'] ?? '00:00:00');
                });
            });

            foreach ($options as $key => $value) {
                switch ($key) {
                    case 'income_admin_name':
                        $admin_ids = Admin::where('school_id', session('school_id'))->where('admin_name', 'like', "%$value%")->pluck('id');
                        $income_ids = Income::whereIn('handle_admin_id', $admin_ids)->pluck('id');
                        $query->whereIn('income_id', $income_ids);
                        break;
                    case 'finish_date':
                        $query->where('date', '<=', $value);
                        break;
                    case 'start_date':
                        $query->where('date', '>=', $value);
                        break;
                    case 'user_truename':
                        $user_ids = User::where('user_truename', 'like', "%$value%")->pluck('id');
                        $query->whereIn('user_id', $user_ids);
                        break;
                    case 'id_card':
                        $user_ids = User::where('id_card', 'like', "%$value%")->pluck('id');
                        $query->whereIn('user_id', $user_ids);
                        break;
                    case 'user_telphone':
                        $user_ids = User::where('user_telphone', 'like', "%$value%")->pluck('id');
                        $query->whereIn('user_id', $user_ids);
                        break;
                    case 'product_id':
                        $user_ids = UserProduct::where('product_id', $value)->pluck('user_id');
                        $query->whereIn('user_id', $user_ids);
                        break;
                    case 'student_id':
                        $user_ids = UserProduct::where('student_id', 'like', "%$value%")->pluck('user_id');
                        $query->whereIn('user_id', $user_ids);
                        break;
                    case 'licence_type_id':
                        $product_ids = Product::where('school_id', session('school_id'))->where('licence_type_id', $value)->pluck('id');
                        $user_ids = UserProduct::whereIn('product_id', $product_ids)->pluck('user_id');
                        $query->whereIn('user_id', $user_ids);
                        break;
                    case 'appointment_type_id':
                        $query->where('type_id', $value);
                        break;
                    default:
                        ;
                }
            }

        })->distinct()->orderBy('date', 'desc')->orderBy('admin_id')->orderBy('user_id');

        return $query;
    }


    // 学员结构统计
    public static function students_distributed($options = [])
    {
        $query = User::leftJoin('user_product', 'user_product.user_id', '=', 'user.id')
            ->whereNotNull('user_product.id');
        foreach ($options as $key => $value) {
            switch ($key) {
                case 'province_id':
                    $school_ids = School::where('province_id', $value)->pluck('id');
                    $query->whereIn('user.school_id', $school_ids);
                    break;
                case 'city_id':
                    $school_ids = School::where('city_id', $value)->pluck('id');
                    $query->whereIn('user.school_id', $school_ids);
                    break;
                case 'district_id':
                    $school_ids = School::where('district_id', $value)->pluck('id');
                    $query->whereIn('user.school_id', $school_ids);
                    break;
                case 'school_id':
                    $school_ids = School::where('id', $value)->pluck('id');
                    $query->whereIn('user.school_id', $school_ids);
                    break;

                case 'start_time':
                    $query->where('user_product.start_date', '>=', $value);
                    break;
                case 'receive_time':
                    $query->where('user_product.start_date', '<=', $value);
                    break;
                default:
                    ;
            }
        }
        // 所有符合的用户
        $user_id = $query->pluck('user.id');

        // 总数
        $students_data['total'][] = count($user_id) ?? 0;
        $students_data['total'][] = UserProduct::whereIn('user_id', $user_id)
            ->where('status', UserProduct::STATUS_REGISTERED)
            ->count();
        // 增加
        $students_data['increase'][] = UserProduct::whereIn('user_id', $user_id)
            ->where('status', UserProduct::STATUS_USING)
            ->where('start_date', date('Y-m-d'))
            ->count();
        $students_data['increase'][] = UserProduct::whereIn('user_id', $user_id)
            ->where('status', UserProduct::STATUS_FINISHED)
            ->where('skill_time', date('Y-m-d'))
            ->count();

        // 男女比
        $students_data['gender'][] = User::whereIn('id', $user_id)
            ->where('user_sex', 1)->count();
        $students_data['gender'][] = $students_data['total'][0] - $students_data['gender'][0];
        // 年龄分布
        $students_data['age'][] = User::whereIn('id', $user_id)
            ->where('birthday', '>=', date('Y-m-d', strtotime('-25 years')))
            ->count();
        $students_data['age'][] = User::whereIn('id', $user_id)
            ->where('birthday', '<', date('Y-m-d', strtotime('-25 years')))
            ->where('birthday', '>=', date('Y-m-d', strtotime('-35 years')))
            ->count();
        $students_data['age'][] = User::whereIn('id', $user_id)
            ->where('birthday', '<', date('Y-m-d', strtotime('-35 years')))
            ->where('birthday', '>=', date('Y-m-d', strtotime('-45 years')))
            ->count();
        $students_data['age'][] = User::whereIn('id', $user_id)
            ->where('birthday', '<', date('Y-m-d', strtotime('-45 years')))
            ->where('birthday', '>=', date('Y-m-d', strtotime('-55 years')))
            ->count();
        $students_data['age'][] = User::whereIn('id', $user_id)
            ->where('birthday', '<', date('Y-m-d', strtotime('-55 years')))
            ->count();
        // 周期
        $students_data['cycle'][] = UserProduct::whereIn('user_id', $user_id)
            ->where('start_date', '>=', date('Y-m-d', strtotime('-1 months')))
            ->count();
        $students_data['cycle'][] = UserProduct::whereIn('user_id', $user_id)
            ->where('start_date', '<', date('Y-m-d', strtotime('-1 months')))
            ->where('start_date', '>=', date('Y-m-d', strtotime('-3 months')))
            ->count();
        $students_data['cycle'][] = UserProduct::whereIn('user_id', $user_id)
            ->where('start_date', '<', date('Y-m-d', strtotime('-3 months')))
            ->where('start_date', '>=', date('Y-m-d', strtotime('-6 months')))
            ->count();
        $students_data['cycle'][] = UserProduct::whereIn('user_id', $user_id)
            ->where('start_date', '<', date('Y-m-d', strtotime('-6 months')))
            ->where('start_date', '>=', date('Y-m-d', strtotime('-12 months')))
            ->count();
        $students_data['cycle'][] = UserProduct::whereIn('user_id', $user_id)
            ->where('start_date', '<', date('Y-m-d', strtotime('-12 months')))
            ->where('start_date', '>=', date('Y-m-d', strtotime('-18 months')))
            ->count();
        $students_data['cycle'][] = UserProduct::whereIn('user_id', $user_id)
            ->where('start_date', '<', date('Y-m-d', strtotime('-18 months')))
            ->count();
        // 每日结业
        $students_data['daily'][0][] = UserProduct::whereIn('user_id', $user_id)
            ->where('status', UserProduct::STATUS_FINISHED)
            ->where('skill_time', date('Y-m-d', strtotime('-1 days')))
            ->count();
        $students_data['daily'][0][] = UserProduct::whereIn('user_id', $user_id)
            ->where('status', UserProduct::STATUS_FINISHED)
            ->where('skill_time', date('Y-m-d', strtotime('-2 days')))
            ->count();
        $students_data['daily'][0][] = UserProduct::whereIn('user_id', $user_id)
            ->where('status', UserProduct::STATUS_FINISHED)
            ->where('skill_time', date('Y-m-d', strtotime('-3 days')))
            ->count();
        $students_data['daily'][0][] = UserProduct::whereIn('user_id', $user_id)
            ->where('status', UserProduct::STATUS_FINISHED)
            ->where('skill_time', date('Y-m-d', strtotime('-4 days')))
            ->count();
        $students_data['daily'][0][] = UserProduct::whereIn('user_id', $user_id)
            ->where('status', UserProduct::STATUS_FINISHED)
            ->where('skill_time', date('Y-m-d', strtotime('-5 days')))
            ->count();
        $students_data['daily'][0][] = UserProduct::whereIn('user_id', $user_id)
            ->where('status', UserProduct::STATUS_FINISHED)
            ->where('skill_time', date('Y-m-d', strtotime('-6 days')))
            ->count();
        $students_data['daily'][0][] = UserProduct::whereIn('user_id', $user_id)
            ->where('status', UserProduct::STATUS_FINISHED)
            ->where('skill_time', date('Y-m-d', strtotime('-7 days')))
            ->count();
        // 每日新增学员
        $students_data['daily'][1][] = UserProduct::whereIn('user_id', $user_id)
            ->where('start_date', date('Y-m-d', strtotime('-1 days')))
            ->count();
        $students_data['daily'][1][] = UserProduct::whereIn('user_id', $user_id)
            ->where('start_date', date('Y-m-d', strtotime('-2 days')))
            ->count();
        $students_data['daily'][1][] = UserProduct::whereIn('user_id', $user_id)
            ->where('start_date', date('Y-m-d', strtotime('-3 days')))
            ->count();
        $students_data['daily'][1][] = UserProduct::whereIn('user_id', $user_id)
            ->where('start_date', date('Y-m-d', strtotime('-4 days')))
            ->count();
        $students_data['daily'][1][] = UserProduct::whereIn('user_id', $user_id)
            ->where('start_date', date('Y-m-d', strtotime('-5 days')))
            ->count();
        $students_data['daily'][1][] = UserProduct::whereIn('user_id', $user_id)
            ->where('start_date', date('Y-m-d', strtotime('-6 days')))
            ->count();
        $students_data['daily'][1][] = UserProduct::whereIn('user_id', $user_id)
            ->where('start_date', date('Y-m-d', strtotime('-7 days')))
            ->count();

        return $students_data;
    }

    /*========Joe=========*/
    public function admininfo()
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }

    public function userinfo()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public static function get_allinfo($where)
    {
        return self::whereIn($where)->groupBy('user_id')->get();
    }

}





