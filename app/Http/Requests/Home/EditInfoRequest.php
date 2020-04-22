<?php

namespace App\Http\Requests\Home;

use App\Http\Requests\Request;

class EditInfoRequest extends Request {
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize() {
        return TRUE;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules() {
        return [
            //
            'user_name'       => 'sometimes|bail|required|alpha_dash|digits_between:6,8',
            'user_truename'   => 'sometimes|bail|required',
            'user_sex'        => 'sometimes|bail|required',
            'id_card'    => 'sometimes|bail|required|unique:user|regex:/^\d{17}(\d|x|X)$/',
            'old_province_id' => 'sometimes|bail|required|integer',
            'old_city_id'     => 'sometimes|bail|required|integer',
            'old_area_id'     => 'sometimes|bail|required|integer',
            'new_province_id' => 'sometimes|bail|required|integer',
            'new_city_id'     => 'sometimes|bail|required|integer',
            'new_area_id'     => 'sometimes|bail|required|integer',
            'user_address'    => 'sometimes|bail|required|string',
            'user_email'      => 'sometimes|bail|email|unique:user,'.$this->id,
        ];
    }

    public function messages() {
        return [
            'required'          => '该选项必须填写',
            'alpha_dash'        => '请输入字母、数字、下划线',
            'digits_between'    => '长度不符合要求',
            'confirmed'         => '两次密码不一致',
            'accepted'          => '请同意协议',
            'integer'           => '必须是数字',
            'string'            => '姓名格式不正确',
            'email'             => '邮箱格式不正确',
            'user_email.unique' => '邮箱已被注册',
        ];
    }
}
