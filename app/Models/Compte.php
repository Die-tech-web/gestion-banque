<?php

namespace App\Models;

use App\Models\Scopes\CompteScopes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Compte extends Model
{
    use HasFactory, SoftDeletes, CompteScopes;

    protected $fillable = [
        'numeroCompte',
        'client_id',
        'type',
        'devise',
        'dateCreation',
        'statut',
        'derniereModification',
        'version',
    ];

    /**
     * Scope a query to only include active comptes.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActif($query)
    {
        return $query->where('statut', 'actif');
    }

    protected $casts = [
        'dateCreation' => 'date',
        'derniereModification' => 'datetime',
    ];

    // Relation avec Client
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    // Relation avec Transactions
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    // Accesseur pour le solde calculé à la volée
    public function getSoldeAttribute()
    {
        $totalDepots = $this->transactions()->where('type', 'depot')->sum('montant');
        $totalRetraits = $this->transactions()->where('type', 'retrait')->sum('montant');
        return $totalDepots - $totalRetraits;
    }
}
