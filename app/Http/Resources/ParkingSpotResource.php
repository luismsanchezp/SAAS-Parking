<?php

namespace App\Http\Resources;

use App\Models\ParkingLot;
use Illuminate\Http\Resources\Json\JsonResource;

class ParkingSpotResource extends JsonResource
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
            'id' => $this->id,
            'row' => $this->row,
            'column' => $this->column,
            'parking_lot' => new ParkingLotResource($this->whenLoaded('parking_lot')),
            'tickets' => TicketResource::collection($this->whenLoaded('tickets'))
        ];
    }
}
