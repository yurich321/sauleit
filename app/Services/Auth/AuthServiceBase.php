<?php

namespace App\Services\Auth;

use App\DTO\UserDTO;

abstract class AuthServiceBase
{
    protected string $table;
    protected string $prefix;
    private ?UserDTO $current = null;

    public function __construct(string $table, string $prefix)
    {
        $this->table  = $table;
        $this->prefix = $prefix;
    }

    private function key(string $k): string
    {
        return $this->prefix . '_' . $k;
    }

    // select from tables
    abstract protected function selectPublic(): string;

    // convert row to DTO
    abstract protected function toDTO(array $row): UserDTO;

    public function user(): ?UserDTO
    {
        if ($this->current) {
            return $this->current;
        }

        $id = $_SESSION[$this->key('uid')] ?? null;

        if (empty($_SESSION[$this->key('uid')])) {
            return null;
        }

        $user = db()->fetchOne("{$this->selectPublic()} WHERE u.id = :id", [':id' => (int)$id]);

        if (!$user) {
            return null;
        }

        return $this->current = $this->toDTO($user);
    }

    public function authenticate(string $email, string $password): ?UserDTO
    {
        $user = db()->fetchOne("{$this->selectPublic()} WHERE email = :e", [':e' => $email]);

        if (!$user || !password_verify($password, $user['password_hash'] ?? '')) {
            return null;
        }

        $_SESSION[$this->key('uid')] = (int)$user['id'];

        return $this->current = $this->toDTO($user);

        //return new UserDTO((int)$user['id'], $user['email'], $user['name'], $user['role_name'] ?? null, $user['home_path'] ?? null);
    }

    public function logout(): void
    {
        //unset($_SESSION[$this->key('uid')]);
        session_destroy();
    }
}