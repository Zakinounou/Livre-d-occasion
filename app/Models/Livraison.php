<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Livraison extends Model
{
    use HasFactory;

    protected $table = 'livraison';
    protected $fillable = [
        'datelivr',
        'heure',
        'etat',
        'dist',
        'idlivrr',
        'idloc',
        'idcom',
    ];
public $timestamps=false;

}
