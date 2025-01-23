<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Board extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'project_id',
        'position',
    ];

    /**
     * The project that the board belongs to.
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * The tasks that belong to the board.
     */
    public function tasks()
    {
        return $this->hasMany(Task::class)->orderBy('position');
    }
}
