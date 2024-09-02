<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RegistrationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $data = parent::toArray($request);
        $data['created_at'] = date('Y/m/d H:i:s', strtotime($this->created_at));
        $data['updated_at'] = date('Y/m/d H:i:s', strtotime($this->updated_at));
        return $data;
    }
}
