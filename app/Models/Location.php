<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Location extends Model
{
    protected $fillable = [
        'external_id',
        'name',
        'type',
        'dimension',
    ];

    public function originCharacters(): HasMany
    {
        return $this->hasMany(Character::class, 'origin_location_id');
    }

    public function currentCharacters(): HasMany
    {
        return $this->hasMany(Character::class, 'current_location_id');
    }
}