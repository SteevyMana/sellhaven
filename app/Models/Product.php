<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\LogsActivity;

class Product extends Model
{
    use LogsActivity;
    protected $fillable = ['name', 'price', 'stock', 'category_id'];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function purchaseDetails()
    {
        return $this->hasMany(PurchaseDetail::class);
    }

    public function stockEntryDetails()
    {
        return $this->hasMany(StockEntryDetail::class);
    }

    public function returnToSupplierDetails()
    {
        return $this->hasMany(ReturnToSupplierDetail::class);
    }

    public function customerReturnDetails()
    {
        return $this->hasMany(CustomerReturnDetail::class);
    }
}
