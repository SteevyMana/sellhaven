<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\LogsActivity;

class Sale extends Model
{
    use LogsActivity;
    protected $fillable = ['customer_id', 'payment_method', 'status', 'date', 'amount_paid', 'total'];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function details()
    {
        return $this->hasMany(SaleDetail::class);
    }
}
