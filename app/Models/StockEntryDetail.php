<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockEntryDetail extends Model
{
    protected $fillable = ['stock_entry_id', 'product_id', 'quantity'];

    public function stockEntry()
    {
        return $this->belongsTo(StockEntry::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
