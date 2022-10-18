<?php

namespace App\Http\Requests\api\v1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

use App\Enums\GovIdTypeEnum;

class ParkingLotUpdateRequest extends FormRequest
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
            "name" => "required|string|min:3|max:25|alpha",
            "surname" => "required|string|min:3|max:25|alpha",
            "id_type" => ["required","string","min:4","max:4",new Enum(GovIdTypeEnum::class)],
            "gov_id" => "required|string|digits_between:10,10|numeric|unique:persons,gov_id",
            "phone_number" => "required|string|digits_between:10,10|numeric|unique:persons,phone_number"
        ];
    }

    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json($validator->errors(), 422));
    }
}
