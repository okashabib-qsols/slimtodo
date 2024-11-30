<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Todo extends Model
{
    protected $table = 'todos';

    protected $fillable = [
        'description',
        'isDone',
        'ItemPosition',
        'color'
    ];

    public $timestamps = false;
}