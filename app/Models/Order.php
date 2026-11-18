<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Order extends Model
{
    public const STATUSES = ['pending', 'paid', 'cancelled'];

    protected $fillable = ['customer_id', 'reference', 'status', 'total'];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /** Tenant isolation: non-admins only ever see their own company's orders. */
    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        return $user->isAdmin()
            ? $query
            : $query->where('company_id', $user->company_id);
    }

    /** Prefix search keeps the LIKE index-friendly. Values are always bound. */
    public function scopeSearch(Builder $query, string $term): Builder
    {
        return $query->where(fn (Builder $q) => $q
            ->where('reference', 'like', "{$term}%")
            ->orWhereHas('customer', fn (Builder $c) => $c->where('name', 'like', "{$term}%")));
    }
}
