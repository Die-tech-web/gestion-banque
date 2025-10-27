<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

trait CompteScopes
{
    /**
     * Apply common filters, sorting, and pagination to the query.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeApplyFiltersAndPagination(Builder $query, Request $request): Builder
    {
        $query->with('client.user');

        // Filtering
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }
        if ($request->has('statut')) {
            $query->where('statut', $request->statut);
        }
        if ($request->has('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('numeroCompte', 'like', '%' . $searchTerm . '%')
                    ->orWhereHas('client.user', function ($q2) use ($searchTerm) {
                        $q2->where('name', 'like', '%' . $searchTerm . '%');
                    });
            });
        }

        // Sorting
        $sort = $request->get('sort', 'dateCreation');
        $order = $request->get('order', 'desc');

        if ($sort === 'titulaire') {
            $query->join('clients', 'comptes.client_id', '=', 'clients.id')
                ->join('users', 'clients.user_id', '=', 'users.id')
                ->orderBy('users.name', $order)
                ->select('comptes.*'); // Select comptes columns to avoid ambiguity
        } elseif ($sort === 'solde') {
            
            $query->orderBy('dateCreation', $order);
        } else {
            $query->orderBy($sort, $order);
        }

        return $query;
    }

    /**
     * Scope to filter comptes by user permissions (admin sees all, client sees only their own).
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  \Illuminate\Contracts\Auth\Authenticatable  $user
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeApplyUserPermissions(Builder $query, $user): Builder
    {
        $isAdmin = $user->admin()->exists();

        if (!$isAdmin) {
            $client = $user->client;
            if (!$client) {
                // Return empty query if client doesn't exist
                return $query->whereRaw('1 = 0');
            }
            $query->where('client_id', $client->id);
        }

        return $query;
    }

    /**
     * Scope to filter comptes by archived status.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  bool  $archived
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByArchivedStatus(Builder $query, bool $archived): Builder
    {
        return $query->where('archived', $archived);
    }

    /**
     * Scope a query to retrieve a Compte by its account number.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $numeroCompte
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByNumeroCompte(Builder $query, string $numeroCompte): Builder
    {
        return $query->where('numeroCompte', $numeroCompte);
    }
}
