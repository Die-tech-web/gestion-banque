<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Laravel\Passport\Passport;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        //
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        Passport::tokensCan([
            'create-compte' => 'Créer un nouveau compte bancaire',
            'view-compte' => 'Voir les détails d\'un compte',
            'update-compte' => 'Modifier un compte',
            'delete-compte' => 'Supprimer un compte',
            'block-compte' => 'Bloquer un compte',
            'unblock-compte' => 'Débloquer un compte',
            'view-transactions' => 'Voir les transactions d\'un compte',
        ]);

        Passport::setDefaultScope([
            'create-compte',
            'view-compte',
            'update-compte',
            'delete-compte',
            'block-compte',
            'unblock-compte',
            'view-transactions',
        ]);
    }
}
