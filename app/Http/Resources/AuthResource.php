<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AuthResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    private ?string $token = null;

    public function withToken(?string $token): self
    {
        $this->token = $token;
        return $this;
    }

    public function toArray(Request $request): array
    {
        $data = [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->roles->pluck('name')->first(),
        ];

        if ($this->token) {
            $data['access_token'] = $this->token;
            $data['token_type'] = 'Bearer';
        }

        return $data;
    }
}
