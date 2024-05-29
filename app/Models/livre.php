<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class livre extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'titre',
        'ida',
        'description',
        'anneePublication',
        'category',
        'etatcom',
        'nbex',
        'langue',
        'nbr_page',
        'maison_edition'
   
    ];
}
