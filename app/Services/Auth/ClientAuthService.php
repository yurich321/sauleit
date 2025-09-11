<?php

namespace App\Services\Auth;

use App\DTO\UserDTO;

final class ClientAuthService extends AuthServiceBase
{
    public function __construct()
    {
        parent::__construct('clients', 'client');
    }

    protected function selectPublic(): string
    {
        return 'SELECT u.id, u.name, u.email, u.password_hash FROM clients u';
    }

    protected function toDTO(array $row): UserDTO
    {
        return new UserDTO(
            id:       (int)$row['id'],
            email:    $row['email'] ?? null,
            name:     $row['name'] ?? null,
            homePath: '/dashboard', // Client home path by default
        );
    }
}