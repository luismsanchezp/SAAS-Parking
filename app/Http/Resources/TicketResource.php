<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TicketResource extends JsonResource
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
            'entry_date' => $this->entry_date,
            'remove_date' => $this->remove_date,
            'parking_spot' => new ParkingSpotResource($this->parking_spot),
            'license_plate' => $this->vehicle->license_plate,
            'vehicle_type' => $this->vehicle->vehicle_type->type,
            'tariff' => $this->vehicle->vehicle_type->tariff,
            $this->mergeWhen(!is_null($this->remove_date), [
                'time_elapsed_hours' => ceil((date_diff(date_create($this->entry_date), date_create($this->remove_date))->s)/3600),
                'total' => ($this->vehicle->vehicle_type->tariff)*ceil((date_diff(date_create($this->entry_date), date_create($this->remove_date))->s)/3600),
            ]),
        ];
    }
}
