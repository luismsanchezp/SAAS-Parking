<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class VehicleResource extends JsonResource
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
            'license_plate' => $this->license_plate,
            'color' => $this->color,
            'person_id' => $this->person_id,
            'vehicle_type_id' => $this->vehicle_type_id,
            'tickets' => TicketResource::collection($this->tickets),
        ];
    }
}
