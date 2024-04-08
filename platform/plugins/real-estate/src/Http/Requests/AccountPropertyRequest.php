<?php

namespace Botble\RealEstate\Http\Requests;

use Botble\RealEstate\Enums\PropertyStatusEnum;
use Botble\RealEstate\Http\Requests\PropertyRequest as BaseRequest;
use Illuminate\Validation\Rule;

class AccountPropertyRequest extends BaseRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name'            => 'required',
            'description'     => 'max:350',
            'content'         => 'required',
            'number_bedroom'  => 'numeric|min:0|max:10000|nullable',
            'number_bathroom' => 'numeric|min:0|max:10000|nullable',
            'number_floor'    => 'numeric|min:0|max:10000|nullable',
            'price'           => 'numeric|min:0|nullable',
            'ropani'             => 'numeric|min:0|nullable',
            'aana'             => 'numeric|min:0|nullable',
            'paisa'             => 'numeric|min:0|nullable',
            'daam'             => 'numeric|min:0|nullable',
            'house_type' => 'nullable',
            'available' => 'nullable',
            'furnishing' => 'nullable',
            'property_type' => 'nullable',
            'status'          => Rule::in(PropertyStatusEnum::values()),
        ];
    }
}
