<?php

namespace App\Models\User;

use Illuminate\Support\Collection;

class Gender
{
    public int $id;
    public string $name;
    public string $gender_name;

    public function __construct(int $id = 1, string $name = 'Laki-laki')
    {
        $this->id = $id;
        $this->name = $name;
        $this->gender_name = $name;
    }

    public static function firstOrCreate(array $attributes = [], array $values = []): self
    {
        $name = $attributes['name'] ?? $values['name'] ?? 'Laki-laki';
        $isFemale = in_array(strtolower($name), ['wanita', 'perempuan', 'female']);
        return new self($isFemale ? 2 : 1, $isFemale ? 'Perempuan' : 'Laki-laki');
    }

    public static function find($id): ?self
    {
        if ($id == 2) {
            return new self(2, 'Perempuan');
        }
        return new self(1, 'Laki-laki');
    }

    public static function all(): Collection
    {
        return collect([
            new self(1, 'Laki-laki'),
            new self(2, 'Perempuan'),
        ]);
    }
}
