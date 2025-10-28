<?php

namespace App\Models;

use App\Models\Scopes\CompteScopes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Compte extends Model
{
    use HasFactory, SoftDeletes, CompteScopes;

    /**
     * The data type of the auto-incrementing ID.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * Indicates if the model's ID is auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });
    }

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
        $solde = $totalDepots - $totalRetraits;

        // Pour les comptes chèque, permettre les soldes négatifs (découvert)
        // Pour les comptes épargne, ne pas afficher de solde négatif (minimum 0)
        // Pour éviter les soldes à 0, on peut retourner un minimum de 10000 pour les comptes sans transactions
        if ($this->transactions()->count() == 0) {
            return 10000; // Minimum 10000 FCFA pour les comptes sans transactions
        }

        if ($this->type === 'epargne' && $solde < 0) {
            return 0;
        }

        return $solde;
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
