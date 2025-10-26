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
        'motifBlocage',
        'dateBlocage',
        'dateDeblocagePrevue',
        'derniereModification',
        'version',
        'dateFermeture',
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
        'dateFermeture' => 'datetime',
        'dateBlocage' => 'datetime',
        'dateDeblocagePrevue' => 'datetime',
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

    /**
     * Vérifier et débloquer automatiquement les comptes dont la date de déblocage est dépassée.
     *
     * @return void
     */
    public function checkAndUnblockExpired()
    {
        if ($this->statut === 'bloque' && $this->dateDeblocagePrevue && now()->greaterThanOrEqualTo($this->dateDeblocagePrevue)) {
            $this->statut = 'actif';
            $this->motifBlocage = null;
            $this->dateBlocage = null;
            $this->dateDeblocagePrevue = null;
            $this->derniereModification = now();
            $this->save();
        }
    }

    /**
     * Scope pour vérifier et débloquer automatiquement les comptes expirés dans une collection.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
     public static function checkExpiredBlocks()
     {
         self::where('statut', 'bloque')
               ->whereNotNull('dateDeblocagePrevue')
               ->where('dateDeblocagePrevue', '<=', now())
               ->update([
                   'statut' => 'actif',
                   'motifBlocage' => null,
                   'dateBlocage' => null,
                   'dateDeblocagePrevue' => null,
                   'derniereModification' => now(),
               ]);
     }
}
