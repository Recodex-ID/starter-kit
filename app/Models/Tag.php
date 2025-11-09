<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class Tag extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'color',
    ];

    // Relationships
    public function posts(): BelongsToMany
    {
        return $this->belongsToMany(Post::class)
            ->withTimestamps();
    }

    // Accessors & Mutators
    public function setNameAttribute($value): void
    {
        $this->attributes['name'] = $value;
        $this->attributes['slug'] = Str::slug($value);
    }

    // Scopes
    public function scopePopular($query, $limit = 10)
    {
        return $query->withCount('posts')
            ->orderBy('posts_count', 'desc')
            ->limit($limit);
    }
}
