<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PersonResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' =>$this->id,
            'name' => $this->name,
            'surname' => $this->surname,
            'id_type' => $this->id_type,
            'gov_id' => $this->gov_id,
            'phone_number' => $this->phone_number,
            'parking_lot_id' => $this->parking_lot_id,
        ];
    }
}
