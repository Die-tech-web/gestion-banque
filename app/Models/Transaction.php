<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'compte_id',
        'type',
        'montant',
        'devise',
        'description',
        'dateTransaction',
    ];

    protected $casts = [
        'dateTransaction' => 'datetime',
    ];

    public function compte()
    {
        return $this->belongsTo(Compte::class);
    }
}
