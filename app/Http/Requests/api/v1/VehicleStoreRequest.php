<?php

namespace App\Http\Requests\api\v1;

use App\Enums\VehicleTypeEnum;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rules\Enum;

class VehicleStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            "license_plate" => "required|string|min:6|max:10|alpha_num|unique:vehicles,license_plate",
            "color" => "required|string|min:3|max:256|alpha",
            "vehicle_type" => ["required","string","min:3","max:9",new Enum(VehicleTypeEnum::class)],
        ];
    }

    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json($validator->errors(),
            422));
    }
}
