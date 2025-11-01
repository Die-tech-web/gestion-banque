<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="Transaction",
 *     title="Transaction",
 *     description="Modèle de transaction",
 *     @OA\Property(
 *         property="id",
 *         type="string",
 *         format="uuid",
 *         description="ID de la transaction",
 *         readOnly=true
 *     ),
 *     @OA\Property(
 *         property="compte_id",
 *         type="string",
 *         format="uuid",
 *         description="ID du compte associé à la transaction"
 *     ),
 *     @OA\Property(
 *         property="type",
 *         type="string",
 *         enum={"depot", "retrait"},
 *         description="Type de transaction (dépôt ou retrait)"
 *     ),
 *     @OA\Property(
 *         property="montant",
 *         type="number",
 *         format="float",
 *         description="Montant de la transaction"
 *     ),
 *     @OA\Property(
 *         property="devise",
 *         type="string",
 *         description="Devise de la transaction",
 *         example="XOF"
 *     ),
 *     @OA\Property(
 *         property="description",
 *         type="string",
 *         nullable=true,
 *         description="Description de la transaction"
 *     ),
 *     @OA\Property(
 *         property="date",
 *         type="string",
 *         format="date-time",
 *         description="Date et heure de la transaction"
 *     ),
 *     @OA\Property(
 *         property="created_at",
 *         type="string",
 *         format="date-time",
 *         description="Date de création de la transaction",
 *         readOnly=true
 *     ),
 *     @OA\Property(
 *         property="updated_at",
 *         type="string",
 *         format="date-time",
 *         description="Date de dernière mise à jour de la transaction",
 *         readOnly=true
 *     )
 * )
 */
class Transaction extends Model
{
    use HasFactory;

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
        'compte_id',
        'type',
        'montant',
        'devise',
        'description',
        'date',
    ];

    protected $casts = [
        'date' => 'datetime',
    ];

    public function compte()
    {
        return $this->belongsTo(Compte::class);
    }
}
