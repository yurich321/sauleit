<?php

namespace App\DTO;

class UserDTO
{
    public int $id;
    public string $email;
    public string $name;
    public ?string $roleName;
    public ?string $homePath;
    public function __construct(int $id, string $email, string $name, ?string $roleName = null, ?string $homePath = null)
    {
        $this->id       = $id;
        $this->email    = $email;
        $this->name     = $name;
        $this->roleName = $roleName ?? null;
        $this->homePath = $homePath ?? null;
    }
}