<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'board_id',
        'position',
        'type',
        'color',
        'description',
        'image_url',
        'image_id',
        'date',
    ];

    public function board()
    {
        return $this->belongsTo(Board::class);
    }
}
