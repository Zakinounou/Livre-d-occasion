<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class exemplaire extends Model
{
    use HasFactory;

    protected $table='exemplaire';
    protected $fillable=[
        'isbn',
        'etat',
        'prix',
        'etat_Com',
    ];
}
