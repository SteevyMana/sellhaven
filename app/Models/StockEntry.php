<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\LogsActivity;

class StockEntry extends Model
{
    use LogsActivity;
    protected $fillable = ['supplier_id', 'purchase_id', 'reason', 'reference', 'date', 'note'];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }

    public function details()
    {
        return $this->hasMany(StockEntryDetail::class);
    }
}
