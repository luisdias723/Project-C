<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        // dd($request);

        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'group_id' => $this->group_id,
            'available_hours' => @unserialize($this->available_hours) ? unserialize($this->available_hours) : [],
            'roles' => $this->name,
            'permissions' =>$this->name,
            // 'roles' => array_map(
            //     function ($role) {
            //         return $role['name'];
            //     },
            //     $this->roles->toArray()
            // ),
            // 'permissions' => array_map(
            //     function ($permission) {
            //         return $permission['name'];
            //     },
            //     $this->getAllPermissions()->toArray()
            // ),
            'mobile_code' => $this->mobile_code,
            'avatar' => $this->profile_photo_path,
            'birthday' => $this->birthday,
            'age' => $this->birthday ? floor((time() - strtotime($this->birthday)) / 31556926) : null,
            'active' => $this->active,
            'address' => $this->address,
            'zipcode' => $this->zipcode,
            'city' => $this->city,
            'country' => $this->country,
            'nif' => $this->nif,
            'iban' => $this->iban,
            'register_completed' => $this->register_completed,
            'is_mastermind' => $this->is_mastermind === 1 ? true : false,
            'colors' => $this->colors,
            'same_invoice_data' => boolval($this->same_invoice_data),
            'invoice_name' => $this->invoice_name,
            'invoice_address' => $this->invoice_address,
            'invoice_iban' => $this->invoice_iban,
            'invoice_nif' => $this->invoice_nif,
            'avg_rating' => number_format((float) $this->avg_rating, 2, '.', ''),
            'tax_regime' => $this->tax_regime,
            'category_id' => $this->category_id,
            'category_name' => $this->category_name,
            'created_at' => $this->created_at ? date_format($this->created_at, 'd-m-Y H:i') : null,
            'coach_coachee' => $this->coach_coachee === 1 ? true : false,
        ];

    }
}

