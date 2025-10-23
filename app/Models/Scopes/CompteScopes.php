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
            // Sorting by calculated balance is complex and usually done in application logic or a view/materialized view
            // For simplicity, we'll skip direct DB sorting by 'solde' here, or assume it's handled post-collection if needed.
            // For now, we'll default to dateCreation if solde is requested for DB sort.
            $query->orderBy('dateCreation', $order);
        } else {
            $query->orderBy($sort, $order);
        }

        return $query;
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
