<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class commande extends Model
{
    use HasFactory;
    protected $table='commande';
    protected $fillable=[
        'avec_livr',
        'idAch',
        'dist', 
        'idpan',
        'idloc',
        'montantTotal'
    ];
    public $timestamps=false;

}
