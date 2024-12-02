<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Todo extends Model
{
    protected $table = 'todos';

    protected $fillable = [
        'description',
        'is_done',
        'item_position',
        'color'
    ];

    public $timestamps = false;
}