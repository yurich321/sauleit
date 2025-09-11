<?php

namespace App\Services\Auth;
use App\DTO\UserDTO;

final class PanelAuthService extends AuthServiceBase
{
    public function __construct()
    {
        parent::__construct('users', 'panel');
    }

    protected function selectPublic(): string
    {
        return 'SELECT u.id,
                        u.name,
                        u.email,
                        u.password_hash,
                        r.name role_name,
                        r.home_path
                    FROM users u 
                        LEFT JOIN roles r ON r.id = u.role_id';
    }

    protected function toDTO(array $row): UserDTO
    {
        return new UserDTO(
            id:       (int)$row['id'],
            email:    $row['email'] ?? null,
            name:     $row['name'] ?? null,
            roleName: (string)($row['role_name'] ?? 'admin'),
            homePath: (string)($row['home_path'] ?? '/panel/clients'),
        );
    }
}