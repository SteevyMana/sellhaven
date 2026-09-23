<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\LogsActivity;

class ReturnToSupplier extends Model
{
    use LogsActivity;
    protected $fillable = ['supplier_id', 'purchase_id', 'status', 'date', 'note'];

    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function details()
    {
        return $this->hasMany(ReturnToSupplierDetail::class);
    }
}
