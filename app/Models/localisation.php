<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class localisation extends Model
{
    use HasFactory;

    protected $table = 'localisation';
    protected $fillable=[
        'idAch',
        'direction',
   ];
public $timestamps =false;
}
