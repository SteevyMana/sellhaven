<?php
// app/Models/CustomerReturnDetail.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\LogsActivity;

class CustomerReturnDetail extends Model
{
    use LogsActivity;
    protected $fillable = ['customer_return_id', 'product_id', 'reason', 'qty', 'stock_before', 'stock_after'];

    public function customerReturn() { return $this->belongsTo(CustomerReturn::class); }
    public function product()        { return $this->belongsTo(Product::class); }
}