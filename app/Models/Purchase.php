<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\LogsActivity;

class Purchase extends Model
{
    use LogsActivity;
    protected $fillable = ['supplier_id', 'status', 'date', 'total'];

    protected $appends = ['total_cost'];

    public function getTotalCostAttribute(): float
    {
        return (float) $this->total;
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function details()
    {
        return $this->hasMany(PurchaseDetail::class);
    }

    public function stockEntries()
    {
        return $this->hasMany(StockEntry::class);
    }

    public function returnsToSupplier()
    {
        return $this->hasMany(ReturnToSupplier::class);
    }
}