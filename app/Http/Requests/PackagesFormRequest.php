<?php

namespace App\Http\Requests;

use App\Http\Requests\Request;

class PackagesFormRequest extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required',
            'training_cost'=>'required|integer',
            'subject_two' => 'required|integer'
        ];
    }
    public function messages() {
        return [
            'required'          => '该选项必须填写',
            'integer'           => '必须是数字',
        ];
    }
}
