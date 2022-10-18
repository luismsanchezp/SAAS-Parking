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
            'vehicle_id' => $this->vehicle_id,
        ];
    }
}
