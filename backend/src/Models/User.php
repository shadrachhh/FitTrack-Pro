<?php

namespace App\Models;

class User
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly string $email,
        public readonly string $password,
        public readonly string $role,
        public readonly string $createdAt
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            (int) $data['id'],
            $data['name'],
            $data['email'],
            $data['password'],
            $data['role'] ?? 'user',
            $data['created_at']
        );
    }

    public function toSessionArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->role,
            'created_at' => $this->createdAt,
        ];
    }
}
