<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ecrir extends Model
{
    use HasFactory;

    protected $table='ecrire';

    protected $fillable=[
        'idAu',
        'id'
    ];
    public $timestamps = false;

}
