<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @property int $id
 * @property string $name
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property \Illuminate\Database\Eloquent\Collection<Todo> $todos
 */
class Tag extends Model
{
    protected $fillable = ['name'];

    /**
     * Get the todos associated with the tag.
     *
     * @return BelongsToMany
     */
    public function todos(): BelongsToMany
    {
        return $this->belongsToMany(Todo::class);
    }
}
