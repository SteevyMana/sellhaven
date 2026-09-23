<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\LogsActivity;

class Supplier extends Model
{
    use LogsActivity;
    protected $fillable = ['name', 'contact', 'email', 'phone', 'city', 'category', 'status'];

    public function purchases()
    {
        return $this->hasMany(Purchase::class);
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
