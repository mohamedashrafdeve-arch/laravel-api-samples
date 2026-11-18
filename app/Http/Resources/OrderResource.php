<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'reference'  => $this->reference,
            'status'     => $this->status,
            'total'      => $this->total,
            'customer'   => $this->whenLoaded('customer', fn () => [
                'id'   => $this->customer->id,
                'name' => $this->customer->name,
            ]),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
