<?php

namespace Botble\RealEstate\Http\Requests;

use Botble\Support\Http\Requests\Request;

class RegisterRequest extends Request
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'first_name' => 'nullable|max:120|min:2',
            'last_name'  => 'nullable|max:120|min:2',
            'username'   => 'nullable|max:60|min:2|unique:re_accounts,username',
            'email'      => 'required|max:60|min:6|email|unique:re_accounts',
            'password'   => 'required|min:8',
        ];
    }
}
