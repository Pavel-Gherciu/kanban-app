<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'user_id'];

    // A Project belongs to a User
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // A Project has many Boards
    public function boards()
    {
        return $this->hasMany(Board::class);
    }
}
