<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\LogsActivity;

class ReturnToSupplierDetail extends Model
{
    use LogsActivity;
    protected $fillable = ['return_to_supplier_id', 'product_id', 'reason', 'qty', 'stock_before', 'stock_after'];

    public function returnToSupplier()
    {
        return $this->belongsTo(ReturnToSupplier::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
