<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Progress extends Model
{

  protected $table = 'progresses';
    protected $fillable = ['total', 'processed', 'status'];
}
